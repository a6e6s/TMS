<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Jobs\TriageTicketJob;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Services\Ai\TicketTriageService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'ai.triage.enabled' => true,
        'ai.triage.model' => 'test-model',
        'ai.triage.api_key' => 'test-key',
        'ai.triage.base_url' => 'https://ai.test/v1',
    ]);
});

function fakeTriageResponse(array $result): void
{
    Http::fake([
        'ai.test/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode($result)]]],
        ]),
    ]);
}

it('masks emails and phone numbers before sending ticket text', function () {
    $service = app(TicketTriageService::class);

    expect($service->redact('Contact sara@client.com or +966 50 123 4567 now'))
        ->toBe('Contact [email] or [phone] now');
});

it('returns null when confidence is below the minimum', function () {
    $service = app(TicketTriageService::class);

    expect($service->parse('{"category":"bug","suggested_priority":"high","confidence":0.2,"summary":"x"}'))
        ->toBeNull();
});

it('parses JSON wrapped in extra prose and rejects unknown categories', function () {
    $service = app(TicketTriageService::class);

    $parsed = $service->parse('Here you go: {"category":"bug","suggested_priority":"high","confidence":0.9,"summary":"Checkout fails."}');

    expect($parsed['category'])->toBe(TicketCategory::Bug)
        ->and($parsed['ai_priority'])->toBe(TicketPriority::High)
        ->and($service->parse('{"category":"spam","suggested_priority":"high","confidence":0.9}'))->toBeNull();
});

it('does not call the AI provider when triage is disabled', function () {
    config(['ai.triage.enabled' => false]);
    Http::fake();

    $ticket = Ticket::factory()->create();
    TriageTicketJob::dispatchSync($ticket->getKey());

    Http::assertNothingSent();
    expect($ticket->fresh()->category)->toBeNull();
});

it('stores the classification without changing the reported priority', function () {
    fakeTriageResponse([
        'category' => 'how_to',
        'suggested_priority' => 'low',
        'confidence' => 0.95,
        'summary' => 'User asks how to export a report.',
    ]);

    $ticket = Ticket::factory()->create(['priority' => TicketPriority::Medium]);
    TriageTicketJob::dispatchSync($ticket->getKey());

    $ticket->refresh();

    expect($ticket->category)->toBe(TicketCategory::HowTo)
        ->and($ticket->ai_priority)->toBe(TicketPriority::Low)
        ->and($ticket->ai_model)->toBe('test-model')
        ->and($ticket->ai_classified_at)->not->toBeNull()
        ->and($ticket->priority)->toBe(TicketPriority::Medium);
});

it('raises to critical only when the model is highly confident and logs it', function () {
    fakeTriageResponse([
        'category' => 'bug',
        'suggested_priority' => 'critical',
        'confidence' => 0.92,
        'summary' => 'Payments fail for all users.',
    ]);

    $ticket = Ticket::factory()->create(['priority' => TicketPriority::Medium]);
    TriageTicketJob::dispatchSync($ticket->getKey());

    expect($ticket->fresh()->priority)->toBe(TicketPriority::Critical);

    $activity = TicketActivity::query()
        ->where('ticket_id', $ticket->getKey())
        ->where('event', 'ai_priority_applied')
        ->firstOrFail();

    expect($activity->properties)->toMatchArray(['from' => 'medium', 'to' => 'critical']);
});

it('keeps the reported priority when critical confidence is below the auto-raise threshold', function () {
    fakeTriageResponse([
        'category' => 'bug',
        'suggested_priority' => 'critical',
        'confidence' => 0.6,
        'summary' => 'Possibly an outage.',
    ]);

    $ticket = Ticket::factory()->create(['priority' => TicketPriority::Medium]);
    TriageTicketJob::dispatchSync($ticket->getKey());

    expect($ticket->fresh()->priority)->toBe(TicketPriority::Medium)
        ->and(TicketActivity::query()->where('event', 'ai_priority_applied')->exists())->toBeFalse();
});

it('never lowers a critical priority', function () {
    fakeTriageResponse([
        'category' => 'how_to',
        'suggested_priority' => 'low',
        'confidence' => 0.99,
        'summary' => 'Question.',
    ]);

    $ticket = Ticket::factory()->create(['priority' => TicketPriority::Critical]);
    TriageTicketJob::dispatchSync($ticket->getKey());

    expect($ticket->fresh()->priority)->toBe(TicketPriority::Critical);
});

it('records an activity when the model returns no usable result', function () {
    Http::fake([
        'ai.test/*' => Http::response(['choices' => [['message' => ['content' => 'not json']]]]),
    ]);

    $ticket = Ticket::factory()->create();
    TriageTicketJob::dispatchSync($ticket->getKey());

    expect($ticket->refresh()->category)->toBe(TicketCategory::Unclear)
        ->and(TicketActivity::query()->where('ticket_id', $ticket->getKey())->where('event', 'ai_triage_no_result')->exists())->toBeTrue();
});

it('records the error message when triage finally fails', function () {
    $ticket = Ticket::factory()->create();

    (new TriageTicketJob($ticket->getKey()))->failed(new RuntimeException('Provider timed out'));

    expect(TicketActivity::query()
        ->where('ticket_id', $ticket->getKey())
        ->where('event', 'ai_triage_failed')
        ->value('properties'))->toBe(['message' => 'Provider timed out']);
});

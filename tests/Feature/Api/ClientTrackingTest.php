<?php

use App\Enums\CommentAuthorType;
use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

function trackedTicket(): array
{
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::InProgress,
        'tracking_token_hash' => hash('sha256', 'reporter-token-123'),
    ]);

    return [$ticket, 'reporter-token-123'];
}

it('returns the ticket status for the reporter with the tracking token', function () {
    [$ticket, $token] = trackedTicket();

    getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => $token])
        ->assertOk()
        ->assertJsonPath('ticket_number', $ticket->ticket_number)
        ->assertJsonPath('status', 'in_progress');
});

it('returns 404 for a wrong or missing tracking token without revealing the ticket', function () {
    [$ticket] = trackedTicket();

    getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => 'wrong'])->assertNotFound();
    getJson('/api/v1/tickets/'.$ticket->ticket_number)->assertNotFound();
    getJson('/api/v1/tickets/TCK-99999', ['X-Tracking-Token' => 'wrong'])->assertNotFound();
});

it('never includes internal notes in the reporter view', function () {
    [$ticket, $token] = trackedTicket();
    Comment::factory()->for($ticket)->create(['body' => 'Internal: escalate to payments', 'is_internal' => true]);
    Comment::factory()->for($ticket)->create(['body' => 'We are looking into it.', 'is_internal' => false]);

    $response = getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => $token])->assertOk();

    expect(collect($response->json('comments'))->pluck('body')->all())
        ->toBe(['We are looking into it.'])
        ->and($response->getContent())->not->toContain('escalate to payments');
});

it('does not expose PMS identifiers to the reporter', function () {
    [$ticket, $token] = trackedTicket();
    $ticket->projectSyncs()->create([
        'pms_provider' => 'custom',
        'external_task_id' => 'SECRET-TASK-1',
        'external_task_url' => 'https://pms.example.com/SECRET-TASK-1',
        'sync_status' => 'synced',
    ]);

    getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => $token])
        ->assertOk()
        ->assertJsonPath('sync.0.state', 'synced')
        ->assertJsonMissingPath('sync.0.external_task_id')
        ->assertDontSee('SECRET-TASK-1');
});

it('lets the reporter post a public comment that is queued for the PMS', function () {
    [$ticket, $token] = trackedTicket();

    postJson('/api/v1/tickets/'.$ticket->ticket_number.'/comments', ['body' => 'Still happening after clearing cache.'], [
        'X-Tracking-Token' => $token,
    ])->assertAccepted()->assertJsonPath('status', 'queued');

    $comment = Comment::query()->where('ticket_id', $ticket->getKey())->firstOrFail();

    expect($comment->is_internal)->toBeFalse()
        ->and($comment->author_type)->toBe(CommentAuthorType::Client)
        ->and($comment->author_id)->toBeNull();
});

it('rejects reporter comments with a wrong tracking token', function () {
    [$ticket] = trackedTicket();

    postJson('/api/v1/tickets/'.$ticket->ticket_number.'/comments', ['body' => 'Hi'], [
        'X-Tracking-Token' => 'wrong',
    ])->assertNotFound();

    expect(Comment::query()->count())->toBe(0);
});

it('rejects reporter attachments on comments until they are supported', function () {
    [$ticket, $token] = trackedTicket();

    postJson('/api/v1/tickets/'.$ticket->ticket_number.'/comments', [
        'body' => 'See screenshot',
        'attachments' => ['file.png'],
    ], ['X-Tracking-Token' => $token])->assertUnprocessable()->assertJsonValidationErrors(['attachments']);
});

it('rate limits client endpoints per tracking token and per IP address', function () {
    [$ticket] = trackedTicket();

    for ($i = 0; $i < 30; $i++) {
        getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => 'same-token']);
    }

    getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => 'same-token'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});

it('rate limits client endpoints per IP when tokens are rotated', function () {
    [$ticket] = trackedTicket();

    for ($i = 0; $i < 60; $i++) {
        getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => 'guess-'.$i]);
    }

    getJson('/api/v1/tickets/'.$ticket->ticket_number, ['X-Tracking-Token' => 'guess-over'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});

it('pushes public reporter comments to the linked PMS task', function () {
    Http::fake(['pms.test/*' => Http::response(['id' => 'T-1'], 201)]);
    config(['pms.base_url' => 'https://pms.test']);

    [$ticket, $token] = trackedTicket();
    $ticket->projectSyncs()->create([
        'pms_provider' => 'custom',
        'external_task_id' => 'T-1',
        'sync_status' => 'synced',
    ]);

    postJson('/api/v1/tickets/'.$ticket->ticket_number.'/comments', ['body' => 'Any update?'], [
        'X-Tracking-Token' => $token,
    ])->assertAccepted();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/tasks/T-1/comments')
        && $request['body'] === 'Any update?');
});

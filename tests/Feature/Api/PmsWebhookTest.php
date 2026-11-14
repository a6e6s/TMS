<?php

use App\Enums\CommentAuthorType;
use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\ProjectSync;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\WebhookPayload;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\postJson;

const WEBHOOK_SECRET = 'whsec-test-value';

/**
 * @param  array<string, mixed>  $body
 * @return array<string, string>
 */
function signedWebhook(array $body, ?int $timestamp = null, string $secret = WEBHOOK_SECRET): array
{
    $timestamp ??= time();
    $raw = json_encode($body, JSON_THROW_ON_ERROR);

    return [
        'headers' => [
            'X-PMS-Timestamp' => (string) $timestamp,
            'X-PMS-Signature' => hash_hmac('sha256', $timestamp.'.'.$raw, $secret),
            'Content-Type' => 'application/json',
        ],
        'raw' => $raw,
    ];
}

function linkedTicket(TicketStatus $status = TicketStatus::Triaged): array
{
    Setting::put('pms.webhook_secret.custom', Crypt::encryptString(WEBHOOK_SECRET));

    $ticket = Ticket::factory()->create(['status' => $status]);
    $sync = ProjectSync::factory()->for($ticket)->create([
        'pms_provider' => 'custom',
        'external_task_id' => 'TASK-42',
    ]);

    return [$ticket, $sync];
}

function sendWebhook(string $provider, array $body, ?int $timestamp = null, ?string $secret = null): TestResponse
{
    $signed = signedWebhook($body, $timestamp, $secret ?? WEBHOOK_SECRET);

    return postJson('/api/v1/webhooks/pms/'.$provider, $body, $signed['headers']);
}

it('applies a verified status change to the linked ticket', function () {
    [$ticket] = linkedTicket(TicketStatus::Triaged);

    sendWebhook('custom', [
        'event_id' => 'evt-1',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'in_progress'],
    ])->assertOk()->assertJsonPath('status', 'processed');

    expect($ticket->refresh()->status)->toBe(TicketStatus::InProgress);

    expect(TicketActivity::query()
        ->where('ticket_id', $ticket->getKey())
        ->where('event', 'status_changed')
        ->exists())->toBeTrue();
});

it('does not process the same event_id twice', function () {
    [$ticket] = linkedTicket(TicketStatus::Triaged);
    $body = ['event_id' => 'evt-dup', 'task_id' => 'TASK-42', 'status' => ['category' => 'in_progress']];

    sendWebhook('custom', $body)->assertOk();
    $ticket->refresh()->update(['status' => TicketStatus::Triaged]);

    sendWebhook('custom', $body)->assertOk()->assertJsonPath('status', 'duplicate');

    expect($ticket->refresh()->status)->toBe(TicketStatus::Triaged)
        ->and(WebhookPayload::query()->where('event_id', 'evt-dup')->count())->toBe(1);
});

it('rejects a webhook with a stale timestamp', function () {
    linkedTicket();

    sendWebhook('custom', [
        'event_id' => 'evt-stale',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'done'],
    ], time() - 600)->assertUnauthorized();
});

it('rejects a webhook with a bad signature', function () {
    [$ticket] = linkedTicket(TicketStatus::Triaged);

    sendWebhook('custom', [
        'event_id' => 'evt-forged',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'in_progress'],
    ], null, 'wrong-secret')->assertUnauthorized();

    expect($ticket->refresh()->status)->toBe(TicketStatus::Triaged);
});

it('rejects a webhook for a provider that is not configured', function () {
    linkedTicket();

    sendWebhook('jira', [
        'event_id' => 'evt-jira',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'done'],
    ])->assertNotFound();
});

it('returns 202 for a task that is not linked to any ticket', function () {
    linkedTicket();

    sendWebhook('custom', [
        'event_id' => 'evt-unknown',
        'task_id' => 'NOPE-1',
        'status' => ['category' => 'done'],
    ])->assertStatus(202)->assertJsonPath('status', 'unknown_task');
});

it('records unmapped PMS statuses without changing the ticket', function () {
    [$ticket] = linkedTicket(TicketStatus::InProgress);

    sendWebhook('custom', [
        'event_id' => 'evt-unmapped',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'someday_maybe'],
    ])->assertOk()->assertJsonPath('status', 'ignored');

    expect($ticket->refresh()->status)->toBe(TicketStatus::InProgress)
        ->and(TicketActivity::query()->where('ticket_id', $ticket->getKey())->where('event', 'pms_status_unmapped')->exists())
        ->toBeTrue();
});

it('ignores a PMS status that would break the ticket lifecycle', function () {
    [$ticket] = linkedTicket(TicketStatus::Resolved);

    sendWebhook('custom', [
        'event_id' => 'evt-invalid',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'backlog'],
    ])->assertOk()->assertJsonPath('status', 'ignored');

    expect($ticket->refresh()->status)->toBe(TicketStatus::Resolved)
        ->and(TicketActivity::query()->where('ticket_id', $ticket->getKey())->where('event', 'pms_status_ignored')->exists())
        ->toBeTrue();
});

it('marks the ticket resolved and posts a public completion note when the PMS task is done', function () {
    [$ticket] = linkedTicket(TicketStatus::InProgress);

    sendWebhook('custom', [
        'event_id' => 'evt-done',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'done'],
    ])->assertOk();

    expect($ticket->refresh()->status)->toBe(TicketStatus::Resolved);

    $note = Comment::query()->where('ticket_id', $ticket->getKey())->where('author_type', CommentAuthorType::System)->firstOrFail();

    expect($note->is_internal)->toBeFalse()
        ->and($note->body)->toBe('Engineering marked the task as completed.');
});

it('rejects a payload missing the required event fields', function () {
    linkedTicket();

    sendWebhook('custom', ['task_id' => 'TASK-42'])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json');
});

it('keeps webhook audit records with a scrubbed payload', function () {
    linkedTicket(TicketStatus::Triaged);

    sendWebhook('custom', [
        'event_id' => 'evt-audit',
        'task_id' => 'TASK-42',
        'status' => ['category' => 'in_progress'],
        'password' => 'do-not-store',
    ])->assertOk();

    $payload = WebhookPayload::query()->where('event_id', 'evt-audit')->firstOrFail();

    expect($payload->raw_payload)->not->toHaveKey('password')
        ->and($payload->raw_payload['task_id'])->toBe('TASK-42');
});

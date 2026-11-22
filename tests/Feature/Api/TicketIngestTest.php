<?php

use App\Enums\TicketLocale;
use App\Models\SourceApp;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WebhookPayload;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\postJson;

const TINY_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

function sourceAppWithToken(array $abilities = ['tickets:create']): array
{
    $app = SourceApp::factory()->create();
    $token = $app->serviceUser->createToken('test', $abilities)->plainTextToken;

    return [$app, $token];
}

function ticketPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'title' => 'Checkout button unresponsive on Safari',
        'description' => 'Clicking Pay Now throws a TypeError in the console.',
        'priority' => 'high',
        'page_url' => 'https://app.example.com/checkout?cart=992&token=secret-value',
        'reporter' => ['name' => 'Sara Ali', 'email' => 'sara@client.com'],
        'device_metadata' => [
            'browser' => 'Safari 18.1',
            'password' => 'hunter2',
            'console_errors' => ['TypeError: cart.submit failed for sara@client.com with Bearer abc.def.ghi'],
        ],
        'screenshots' => [TINY_PNG],
    ], $overrides);
}

it('rejects requests without a bearer token', function () {
    postJson('/api/v1/tickets', ticketPayload(), ['Idempotency-Key' => 'k-1'])
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/problem+json');
});

it('creates a ticket and returns a one-time tracking token', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();

    $response = postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-001',
        'Accept-Language' => 'ar',
    ]);

    $response->assertAccepted()
        ->assertJsonPath('status', 'queued')
        ->assertJsonStructure(['ticket_number', 'tracking_url', 'tracking_token']);

    $ticket = Ticket::query()->where('ticket_number', $response->json('ticket_number'))->firstOrFail();

    expect($ticket->source->value)->toBe('api')
        ->and($ticket->locale)->toBe(TicketLocale::Arabic)
        ->and($ticket->priority->value)->toBe('medium')
        ->and($ticket->tracking_token_hash)->toBe(hash('sha256', $response->json('tracking_token')))
        ->and($ticket->reporter_email)->toBe('sara@client.com');
});

it('keeps the reporter browser user agent and viewport on the ticket', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();

    $response = postJson('/api/v1/tickets', ticketPayload([
        'device_metadata' => ['user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)', 'viewport' => '1440x900'],
    ]), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-browser',
    ]);

    $response->assertAccepted();

    $ticket = Ticket::query()->where('ticket_number', $response->json('ticket_number'))->firstOrFail();

    expect($ticket->device_metadata)
        ->toHaveKey('user_agent', 'Mozilla/5.0 (X11; Linux x86_64)')
        ->toHaveKey('viewport', '1440x900');
});

it('returns the original ticket for a repeated Idempotency-Key without creating another', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();
    $headers = ['Authorization' => 'Bearer '.$token, 'Idempotency-Key' => 'key-repeat'];

    $first = postJson('/api/v1/tickets', ticketPayload(), $headers)->assertAccepted();
    $second = postJson('/api/v1/tickets', ticketPayload(), $headers)
        ->assertOk()
        ->assertJsonMissingPath('tracking_token');

    expect($second->json('ticket_number'))->toBe($first->json('ticket_number'))
        ->and(Ticket::query()->count())->toBe(1);
});

it('requires an Idempotency-Key header', function () {
    [, $token] = sourceAppWithToken();

    postJson('/api/v1/tickets', ticketPayload(), ['Authorization' => 'Bearer '.$token])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['Idempotency-Key']);
});

it('returns field-level problem details for invalid payloads', function () {
    [, $token] = sourceAppWithToken();

    postJson('/api/v1/tickets', ['reporter' => ['email' => 'not-an-email']], [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-invalid',
    ])
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('status', 422)
        ->assertJsonValidationErrors(['title', 'description', 'reporter.email']);
});

it('rejects a token that lacks the tickets:create ability', function () {
    [, $token] = sourceAppWithToken(['tickets:read']);

    postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-scope',
    ])->assertForbidden();
});

it('rejects staff accounts from the intake API', function () {
    $staff = User::factory()->superAdmin()->create();
    $token = $staff->createToken('staff', ['tickets:create'])->plainTextToken;

    postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-staff',
    ])->assertForbidden();
});

it('returns 401 once a token is revoked', function () {
    [$app, $token] = sourceAppWithToken();
    $app->serviceUser->tokens()->delete();

    postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-revoked',
    ])->assertUnauthorized();
});

it('rejects payloads over 5 MB with 413', function () {
    [, $token] = sourceAppWithToken();

    postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-large',
        'Content-Length' => (string) (6 * 1024 * 1024),
    ])->assertStatus(413)
        ->assertHeader('Content-Type', 'application/problem+json');
});

it('rejects a screenshot over 2 MB with 413', function () {
    [, $token] = sourceAppWithToken();
    $oversized = 'data:image/png;base64,'.base64_encode(str_repeat('a', 2 * 1024 * 1024 + 1024));

    postJson('/api/v1/tickets', ticketPayload(['screenshots' => [$oversized]]), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-screenshot',
    ])->assertStatus(413);
});

it('stores decoded screenshots on the private local disk', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();

    $response = postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-shot',
    ])->assertAccepted();

    $ticket = Ticket::query()->where('ticket_number', $response->json('ticket_number'))->firstOrFail();

    expect($ticket->attachments)->toHaveCount(1);
    Storage::disk('local')->assertExists($ticket->attachments[0]);
    expect(str_ends_with($ticket->attachments[0], '.png'))->toBeTrue();
});

it('stores screenshots during intake without queueing any job', function () {
    Storage::fake('local');
    Queue::fake();
    [, $token] = sourceAppWithToken();

    $response = postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-sync-shot',
    ])->assertAccepted();

    $ticket = Ticket::query()->where('ticket_number', $response->json('ticket_number'))->firstOrFail();

    Queue::assertNothingPushed();
    Storage::disk('local')->assertExists($ticket->attachments[0]);
});

it('ignores screenshots that are not real images', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();
    $fake = 'data:image/png;base64,'.base64_encode('<script>alert(1)</script>');

    postJson('/api/v1/tickets', ticketPayload(['screenshots' => [$fake]]), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-fake',
    ])->assertAccepted();

    $ticket = Ticket::query()->latest('id')->firstOrFail();

    expect($ticket->attachments)->toBeNull();
});

it('stores a scrubbed audit copy of the payload', function () {
    Storage::fake('local');
    [$app, $token] = sourceAppWithToken();

    postJson('/api/v1/tickets', ticketPayload(), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'key-audit',
        'Cookie' => 'session=abc',
    ])->assertAccepted();

    $payload = WebhookPayload::query()->where('event_id', 'key-audit')->firstOrFail();
    $ticket = Ticket::query()->findOrFail($payload->ticket_id);

    expect($payload->source_app_id)->toBe($app->getKey())
        ->and($payload->raw_payload)->not->toHaveKey('screenshots')
        ->and($payload->raw_payload['screenshots_count'])->toBe(1)
        ->and($payload->raw_payload['device_metadata'])->not->toHaveKey('password')
        ->and($payload->headers)->not->toHaveKey('cookie')
        ->and($payload->headers)->not->toHaveKey('authorization')
        ->and($ticket->page_url)->toBe('https://app.example.com/checkout?cart=992')
        ->and($ticket->device_metadata['console_errors'][0])->not->toContain('sara@client.com')
        ->and($ticket->device_metadata['console_errors'][0])->not->toContain('abc.def.ghi');
});

it('is rate limited per source app token', function () {
    Storage::fake('local');
    [, $token] = sourceAppWithToken();

    for ($i = 0; $i < 60; $i++) {
        postJson('/api/v1/tickets', ticketPayload(['screenshots' => []]), [
            'Authorization' => 'Bearer '.$token,
            'Idempotency-Key' => 'burst-'.$i,
        ]);
    }

    postJson('/api/v1/tickets', ticketPayload(['screenshots' => []]), [
        'Authorization' => 'Bearer '.$token,
        'Idempotency-Key' => 'burst-over',
    ])->assertStatus(429)->assertHeader('Retry-After');
});

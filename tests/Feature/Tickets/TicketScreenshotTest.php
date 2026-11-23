<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('local');
});

it('serves a stored screenshot through a signed URL', function () {
    $path = 'tickets/7/screenshots/9b1c2d3e-0000-4000-8000-000000000001.png';
    Storage::disk('local')->put($path, 'png-bytes');
    $ticket = Ticket::factory()->create(['id' => 7, 'attachments' => [$path]]);

    $url = URL::temporarySignedRoute('tickets.screenshot', now()->addMinutes(30), [
        'ticket' => $ticket->getKey(),
        'screenshot' => '9b1c2d3e-0000-4000-8000-000000000001',
    ]);

    $response = get($url)->assertOk();

    expect($response->streamedContent())->toBe('png-bytes');
});

it('rejects a screenshot URL without a valid signature', function () {
    $path = 'tickets/8/screenshots/aaaa-bbbb.png';
    Storage::disk('local')->put($path, 'png-bytes');
    $ticket = Ticket::factory()->create(['id' => 8, 'attachments' => [$path]]);

    get("/ticket-screenshots/{$ticket->getKey()}/aaaa-bbbb")->assertForbidden();
});

it('does not serve a screenshot that belongs to a different ticket', function () {
    $path = 'tickets/9/screenshots/cccc-dddd.png';
    Storage::disk('local')->put($path, 'png-bytes');
    $other = Ticket::factory()->create(['id' => 10, 'attachments' => []]);

    $url = URL::temporarySignedRoute('tickets.screenshot', now()->addMinutes(30), [
        'ticket' => $other->getKey(),
        'screenshot' => 'cccc-dddd',
    ]);

    get($url)->assertNotFound();
});

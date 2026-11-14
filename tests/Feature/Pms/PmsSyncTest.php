<?php

use App\Enums\ProjectSyncStatus;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Jobs\PushTicketToPmsJob;
use App\Models\ProjectSync;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\PmsSyncFailedNotification;
use App\Services\Pms\PmsConnectorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    config(['pms.base_url' => 'https://pms.test', 'pms.api_token' => 'test-token']);
});

it('creates one PMS task per ticket even when the push is dispatched twice', function () {
    Http::fake(['pms.test/tasks' => Http::response(['id' => 'T-100', 'url' => 'https://pms.test/T-100'], 201)]);

    $ticket = Ticket::factory()->create(['status' => TicketStatus::Triaged]);

    PushTicketToPmsJob::dispatch($ticket->getKey(), 'custom');
    PushTicketToPmsJob::dispatch($ticket->getKey(), 'custom');

    Http::assertSentCount(1);

    $sync = ProjectSync::query()->where('ticket_id', $ticket->getKey())->sole();

    expect($sync->external_task_id)->toBe('T-100')
        ->and($sync->sync_status)->toBe(ProjectSyncStatus::Synced)
        ->and($ticket->refresh()->status)->toBe(TicketStatus::PushedToPms);
});

it('records the error on the sync row when a PMS attempt fails', function () {
    Http::fake(['pms.test/tasks' => Http::response(['message' => 'boom'], 500)]);

    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
    $job = new PushTicketToPmsJob($ticket->getKey(), 'custom');

    expect(fn () => $job->handle(app(PmsConnectorInterface::class)))->toThrow(Exception::class);

    $sync = ProjectSync::query()->where('ticket_id', $ticket->getKey())->sole();

    expect($sync->sync_attempts)->toBe(1)
        ->and($sync->last_error)->not->toBeNull()
        ->and($sync->external_task_id)->toBeNull();
});

it('marks the sync failed and notifies super admins after the final attempt', function () {
    Notification::fake();
    $admin = User::factory()->superAdmin()->create();
    $ticket = Ticket::factory()->create();

    $job = new PushTicketToPmsJob($ticket->getKey(), 'custom');
    ProjectSync::factory()->for($ticket)->create([
        'pms_provider' => 'custom',
        'external_task_id' => null,
        'sync_status' => ProjectSyncStatus::Pending,
    ]);

    $job->failed(new Exception('PMS unavailable'));

    expect(ProjectSync::query()->where('ticket_id', $ticket->getKey())->sole()->sync_status)
        ->toBe(ProjectSyncStatus::Failed);

    Notification::assertSentTo($admin, PmsSyncFailedNotification::class);
});

it('pushes a status change made by staff to the linked PMS task', function () {
    Http::fake(['pms.test/tasks/*' => Http::response([], 200)]);
    actingAs(User::factory()->teamLead()->create());

    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
    ProjectSync::factory()->for($ticket)->create(['pms_provider' => 'custom', 'external_task_id' => 'T-7']);

    $ticket->update(['status' => TicketStatus::Triaged]);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && str_ends_with($request->url(), '/tasks/T-7')
        && $request['status'] === 'triaged');
});

it('does not echo webhook-driven status changes back to the PMS', function () {
    Http::fake();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Triaged]);
    ProjectSync::factory()->for($ticket)->create(['pms_provider' => 'custom', 'external_task_id' => 'T-8']);

    $ticket->update(['status' => TicketStatus::InProgress]);

    Http::assertNothingSent();
});

it('shows the Push to PMS action to staff until a task exists', function () {
    Http::fake(['pms.test/tasks' => Http::response(['id' => 'T-55', 'url' => null], 201)]);
    actingAs(User::factory()->create());

    $ticket = Ticket::factory()->create(['status' => TicketStatus::Triaged]);

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertActionVisible('pushToPms')
        ->callAction('pushToPms');

    expect(ProjectSync::query()->where('ticket_id', $ticket->getKey())->value('external_task_id'))->toBe('T-55');

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertActionHidden('pushToPms');
});

it('hides the Push to PMS action for closed tickets', function () {
    actingAs(User::factory()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertActionHidden('pushToPms');
});

it('shows a failed PMS sync and its last error on the ticket page', function () {
    actingAs(User::factory()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Triaged]);
    ProjectSync::factory()->for($ticket)->create([
        'pms_provider' => 'custom',
        'external_task_id' => null,
        'sync_status' => ProjectSyncStatus::Failed,
        'last_error' => 'PMS returned HTTP 503',
    ]);

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertSee('PMS returned HTTP 503');
});

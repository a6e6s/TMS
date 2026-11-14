<?php

use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Filament\Resources\Tickets\Schemas\TicketForm;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('allows the documented status transitions', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    $ticket->update(['status' => TicketStatus::Triaged]);
    $ticket->update(['status' => TicketStatus::InProgress]);
    $ticket->update(['status' => TicketStatus::Resolved]);
    $ticket->update(['status' => TicketStatus::Open]);

    expect($ticket->refresh()->status)->toBe(TicketStatus::Open);
});

it('rejects status jumps that skip the lifecycle', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    expect(fn () => $ticket->update(['status' => TicketStatus::Resolved]))
        ->toThrow(ValidationException::class);

    expect($ticket->refresh()->status)->toBe(TicketStatus::Open);
});

it('blocks support agents from closing an open ticket', function () {
    actingAs(User::factory()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    expect(fn () => $ticket->update(['status' => TicketStatus::Closed]))
        ->toThrow(ValidationException::class);
});

it('lets team leads close an open ticket such as spam or a duplicate', function () {
    actingAs(User::factory()->teamLead()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    $ticket->update(['status' => TicketStatus::Closed]);

    expect($ticket->refresh()->status)->toBe(TicketStatus::Closed);
});

it('lets support agents close a resolved ticket', function () {
    actingAs(User::factory()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Resolved]);

    $ticket->update(['status' => TicketStatus::Closed]);

    expect($ticket->refresh()->status)->toBe(TicketStatus::Closed);
});

it('writes an activity row for each status, priority, and assignee change', function () {
    $actor = User::factory()->teamLead()->create();
    $assignee = User::factory()->create();
    actingAs($actor);
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open, 'priority' => 'medium']);

    $ticket->update([
        'status' => TicketStatus::Triaged,
        'priority' => 'critical',
        'assigned_to_id' => $assignee->id,
    ]);

    $events = TicketActivity::query()
        ->where('ticket_id', $ticket->id)
        ->pluck('event')
        ->all();

    expect($events)->toContain('ticket_created', 'status_changed', 'priority_changed', 'assignee_changed');

    $statusRow = TicketActivity::query()
        ->where('ticket_id', $ticket->id)
        ->where('event', 'status_changed')
        ->firstOrFail();

    expect($statusRow->actor_id)->toBe($actor->id)
        ->and($statusRow->properties)->toBe(['from' => 'open', 'to' => 'triaged']);
});

it('does not write an activity row when nothing relevant changed', function () {
    $ticket = Ticket::factory()->create();

    $before = TicketActivity::query()->where('ticket_id', $ticket->id)->count();
    $ticket->update(['title' => 'Renamed']);

    expect(TicketActivity::query()->where('ticket_id', $ticket->id)->count())->toBe($before);
});

it('shows the transition error on the status field in the Filament edit form', function () {
    actingAs(User::factory()->create());
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    Livewire::test(EditTicket::class, ['record' => $ticket->getRouteKey()])
        ->fillForm(['status' => TicketStatus::Closed->value])
        ->call('save')
        ->assertHasFormErrors(['status']);

    expect($ticket->refresh()->status)->toBe(TicketStatus::Open);
});

it('lists only the transitions allowed from the current status in the form', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

    expect(TicketForm::statusOptions($ticket))
        ->toHaveKeys(['open', 'triaged', 'closed'])
        ->not->toHaveKey('resolved');
});

<?php

use App\Enums\UserRole;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('lets super admins see every ticket', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $otherAgent = User::factory()->create();
    $own = Ticket::factory()->create(['assigned_to_id' => $superAdmin->id, 'title' => 'Own ticket']);
    $foreign = Ticket::factory()->create(['assigned_to_id' => $otherAgent->id, 'title' => 'Foreign ticket']);

    actingAs($superAdmin);

    Livewire::test(ListTickets::class)
        ->assertSeeText($own->ticket_number)
        ->assertSeeText($foreign->ticket_number);
});

it('lets support agents see only their own and unassigned tickets', function () {
    $agent = User::factory()->create();
    $otherAgent = User::factory()->create();
    $own = Ticket::factory()->create(['assigned_to_id' => $agent->id]);
    $unassigned = Ticket::factory()->create(['assigned_to_id' => null]);
    $foreign = Ticket::factory()->create(['assigned_to_id' => $otherAgent->id]);

    actingAs($agent);

    Livewire::test(ListTickets::class)
        ->assertSeeText($own->ticket_number)
        ->assertSeeText($unassigned->ticket_number)
        ->assertDontSeeText($foreign->ticket_number);
});

it('lets team leads see tickets of their direct reports', function () {
    $lead = User::factory()->teamLead()->create();
    $report = User::factory()->create(['reports_to_id' => $lead->id]);
    $stranger = User::factory()->create();
    $reportTicket = Ticket::factory()->create(['assigned_to_id' => $report->id]);
    $strangerTicket = Ticket::factory()->create(['assigned_to_id' => $stranger->id]);

    actingAs($lead);

    Livewire::test(ListTickets::class)
        ->assertSeeText($reportTicket->ticket_number)
        ->assertDontSeeText($strangerTicket->ticket_number);
});

it('returns not found for a ticket outside the agent visibility', function () {
    $agent = User::factory()->create();
    $foreign = Ticket::factory()->create(['assigned_to_id' => User::factory()->create()->id]);

    actingAs($agent);

    expect(Ticket::query()->visibleTo($agent)->whereKey($foreign->id)->exists())->toBeFalse();
    expect(auth()->user()->can('view', $foreign))->toBeFalse();
});

it('allows only super admins to delete tickets', function () {
    $ticket = Ticket::factory()->create();

    actingAs(User::factory()->teamLead()->create());
    expect(auth()->user()->can('delete', $ticket))->toBeFalse();

    actingAs(User::factory()->superAdmin()->create());
    expect(auth()->user()->can('delete', $ticket))->toBeTrue();
});

it('assigns the Spatie super admin role', function () {
    $user = User::factory()->superAdmin()->create();

    expect($user->refresh()->isRole(UserRole::SuperAdmin))->toBeTrue();
});

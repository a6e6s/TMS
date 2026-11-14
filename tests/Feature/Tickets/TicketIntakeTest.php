<?php

use App\Enums\TicketLocale;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('creates a ticket from the Filament intake form', function () {
    Livewire::test(CreateTicket::class)
        ->fillForm([
            'title' => 'Checkout button unresponsive',
            'description' => 'Clicking Pay Now throws a TypeError.',
            'page_url' => 'https://app.example.com/checkout',
            'locale' => TicketLocale::Arabic->value,
            'reporter_name' => 'Sara Ali',
            'reporter_email' => 'sara@client.com',
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::High->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Ticket::class, [
        'title' => 'Checkout button unresponsive',
        'locale' => 'ar',
        'status' => 'open',
        'priority' => 'high',
        'reporter_email' => 'sara@client.com',
    ]);
});

it('requires a title and description', function () {
    Livewire::test(CreateTicket::class)
        ->fillForm([
            'title' => null,
            'description' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'description' => 'required',
        ]);

    expect(Ticket::count())->toBe(0);
});

it('assigns a ticket number in TCK format', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->ticket_number)->toMatch('/^TCK-\d{5}$/');
});

it('writes a creation activity row for every new ticket', function () {
    $ticket = Ticket::factory()->create();

    expect(TicketActivity::query()
        ->where('ticket_id', $ticket->id)
        ->where('event', 'ticket_created')
        ->count())->toBe(1);
});

it('limits ticket languages to Arabic and English', function () {
    expect(collect(TicketLocale::cases())->map->value->all())->toBe(['en', 'ar']);
});

it('can edit an existing ticket from the Filament edit page', function () {
    $ticket = Ticket::factory()->create(['title' => 'Old title']);

    Livewire::test(EditTicket::class, ['record' => $ticket->getRouteKey()])
        ->fillForm(['title' => 'Updated title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($ticket->refresh()->title)->toBe('Updated title');
});

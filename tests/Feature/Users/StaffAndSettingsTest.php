<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Widgets\TicketStatsOverview;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('lets super admins create staff with a role and reporting line', function () {
    $manager = User::factory()->teamLead()->create();
    actingAs(User::factory()->superAdmin()->create());

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New Agent',
            'email' => 'agent@example.com',
            'password' => 'password123',
            'role' => UserRole::SupportAgent->value,
            'reports_to_id' => $manager->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'email' => 'agent@example.com',
        'role' => 'support_agent',
        'reports_to_id' => $manager->id,
    ]);
});

it('forbids team leads from creating staff', function () {
    actingAs(User::factory()->teamLead()->create());

    expect(auth()->user()->can('create', User::class))->toBeFalse();
});

it('lets team leads open the staff list', function () {
    actingAs(User::factory()->teamLead()->create());

    Livewire::test(ListUsers::class)->assertOk();
});

it('forbids support agents from the staff list', function () {
    actingAs(User::factory()->create());

    expect(auth()->user()->can('viewAny', User::class))->toBeFalse();
});

it('does not let a staff member report to themselves', function () {
    $admin = User::factory()->superAdmin()->create();
    actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm(['reports_to_id' => $admin->id])
        ->call('save')
        ->assertHasFormErrors(['reports_to_id']);
});

it('saves ticket settings from the settings page', function () {
    actingAs(User::factory()->superAdmin()->create());

    Livewire::test(ManageSettings::class)
        ->fillForm([
            'auto_close_days' => 21,
            'retention_days' => 120,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('ticket.auto_close_days'))->toBe('21')
        ->and(Setting::get('retention.days'))->toBe('120');
});

it('hides the settings page from non-super-admins', function () {
    actingAs(User::factory()->teamLead()->create());

    expect(ManageSettings::canAccess())->toBeFalse();
});

it('falls back to the default settings when no value is stored', function () {
    expect(Setting::get('ticket.auto_close_days'))->toBe(14)
        ->and(Setting::get('retention.days'))->toBe(90);
});

it('closes resolved tickets that were not reopened within the auto-close window', function () {
    $stale = Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    $stale->forceFill(['updated_at' => now()->subDays(20)])->saveQuietly();

    $recent = Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    $recent->forceFill(['updated_at' => now()->subDays(2)])->saveQuietly();

    $open = Ticket::factory()->create(['status' => TicketStatus::Open]);
    $open->forceFill(['updated_at' => now()->subDays(60)])->saveQuietly();

    $this->artisan('tickets:close-resolved')->assertSuccessful();

    expect($stale->refresh()->status)->toBe(TicketStatus::Closed)
        ->and($recent->refresh()->status)->toBe(TicketStatus::Resolved)
        ->and($open->refresh()->status)->toBe(TicketStatus::Open);
});

it('renders the ticket stats widget for staff', function () {
    actingAs(User::factory()->create());
    Ticket::factory()->create(['status' => TicketStatus::Open]);

    Livewire::test(TicketStatsOverview::class)
        ->assertOk()
        ->assertSee('Open tickets');
});

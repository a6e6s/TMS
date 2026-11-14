<?php

use App\Filament\Resources\SourceApps\Pages\CreateSourceApp;
use App\Filament\Resources\SourceApps\Pages\ListSourceApps;
use App\Models\SourceApp;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Panel;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('creates a service account for each new source app', function () {
    actingAs(User::factory()->superAdmin()->create());

    Livewire::test(CreateSourceApp::class)
        ->fillForm([
            'name' => 'Checkout Web',
            'slug' => 'checkout-web',
            'pms_project_id' => 'PRJ-7',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $app = SourceApp::query()->where('slug', 'checkout-web')->firstOrFail();

    expect($app->serviceUser->user_type->value)->toBe('service')
        ->and($app->serviceUser->canAccessPanel(app(Panel::class)))->toBeFalse()
        ->and($app->pms_project_id)->toBe('PRJ-7');
});

it('issues a token that can create tickets and revokes it on request', function () {
    actingAs(User::factory()->superAdmin()->create());
    $app = SourceApp::factory()->create();

    Livewire::test(ListSourceApps::class)
        ->callAction(TestAction::make('issueToken')->table($app));

    expect($app->serviceUser->tokens()->count())->toBe(1)
        ->and($app->serviceUser->tokens()->first()->abilities)->toBe(['tickets:create']);

    Livewire::test(ListSourceApps::class)
        ->callAction(TestAction::make('revokeTokens')->table($app));

    expect($app->serviceUser->tokens()->count())->toBe(0);
});

it('keeps source app administration away from team leads and agents', function () {
    actingAs(User::factory()->teamLead()->create());

    expect(auth()->user()->can('viewAny', SourceApp::class))->toBeFalse()
        ->and(auth()->user()->can('create', SourceApp::class))->toBeFalse();
});

it('lets staff log in but never service accounts', function () {
    $panel = app(Panel::class);

    expect(User::factory()->create()->canAccessPanel($panel))->toBeTrue()
        ->and(User::factory()->serviceAccount()->create()->canAccessPanel($panel))->toBeFalse();
});

it('stores the issued token against the service account', function () {
    $app = SourceApp::factory()->create();
    $app->serviceUser->createToken('checkout-web', ['tickets:create']);

    assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $app->user_id,
        'name' => 'checkout-web',
    ]);
});

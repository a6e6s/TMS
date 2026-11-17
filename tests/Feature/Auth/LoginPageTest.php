<?php

use App\Filament\Auth\Login;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\get;

it('renders the split-layout login page with the TMS brand for guests', function () {
    get('/admin/login')
        ->assertOk()
        ->assertSee('tms-login-page', false)
        ->assertSee(__('filament/common.brand'));
});

it('renders the login page in Arabic with a right-to-left layout', function () {
    session(['filament_locale' => 'ar']);

    get('/admin/login')
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee(__('filament/common.auth.welcome_heading', [], 'ar'));
});

it('allows the seeded super admin to sign into the admin panel', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::query()
        ->where('email', config('tms.admin.email'))
        ->firstOrFail();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => config('tms.admin.email'),
            'password' => config('tms.admin.password'),
        ])
        ->call('authenticate')
        ->assertHasNoErrors();

    assertAuthenticatedAs($admin);
});

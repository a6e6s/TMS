<?php

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

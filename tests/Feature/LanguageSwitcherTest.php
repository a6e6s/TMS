<?php

use App\Models\User;
use Illuminate\Support\Arr;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('stores the selected locale in the session', function () {
    actingAs(User::factory()->create());

    post(route('admin.language'), ['locale' => 'ar'])
        ->assertRedirect()
        ->assertSessionHas('filament_locale', 'ar');
});

it('rejects locales other than Arabic and English', function () {
    post(route('admin.language'), ['locale' => 'fr'])
        ->assertSessionHasErrors('locale')
        ->assertSessionMissing('filament_locale');
});

it('renders the admin panel right-to-left after switching to Arabic', function () {
    actingAs(User::factory()->create());

    post(route('admin.language'), ['locale' => 'ar']);

    $this->get('/admin/tickets')
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('renders the admin panel left-to-right in English', function () {
    actingAs(User::factory()->create());

    post(route('admin.language'), ['locale' => 'en']);

    $this->get('/admin/tickets')
        ->assertOk()
        ->assertSee('dir="ltr"', false);
});

it('ships matching English and Arabic keys for the ticket resource', function () {
    $english = array_keys(Arr::dot(require lang_path('en/filament/resources/tickets.php')));
    $arabic = array_keys(Arr::dot(require lang_path('ar/filament/resources/tickets.php')));

    expect($arabic)->toEqual($english);
});

it('ships matching English and Arabic keys for shared enums', function () {
    $english = array_keys(Arr::dot(require lang_path('en/filament/enums.php')));
    $arabic = array_keys(Arr::dot(require lang_path('ar/filament/enums.php')));

    expect($arabic)->toEqual($english);
});

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Organization-wide values stored in the "settings" table. A row in the
    | database overrides the default defined here.
    |
    */

    'settings' => [
        'retention.days' => 90,
        'ticket.auto_close_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Tracking URL
    |--------------------------------------------------------------------------
    |
    | Base URL returned to reporters as the tracking_url for each ticket.
    |
    */

    'tracking_url' => env('TMS_TRACKING_URL', env('APP_URL', 'http://localhost')).'/tickets',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The admin panel and ticket content support English and Arabic only.
    |
    */

    'locales' => ['en', 'ar'],

    /*
    |--------------------------------------------------------------------------
    | Seeded Super Admin
    |--------------------------------------------------------------------------
    |
    | Account created by the database seeder. Set ADMIN_PASSWORD in .env
    | before seeding anything other than a local development database.
    |
    */

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

];

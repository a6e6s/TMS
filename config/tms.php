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
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The admin panel and ticket content support English and Arabic only.
    |
    */

    'locales' => ['en', 'ar'],

];

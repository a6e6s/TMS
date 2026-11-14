<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PMS Connector
    |--------------------------------------------------------------------------
    |
    | The external Project Management System. The generic HTTP connector speaks
    | a small REST contract; the vendor-specific connector is chosen per
    | provider key once the first PMS is confirmed (see Plan.md Section 6).
    |
    */

    'default_provider' => env('PMS_PROVIDER', 'custom'),

    'base_url' => env('PMS_BASE_URL'),

    'api_token' => env('PMS_API_TOKEN'),

    'default_project_id' => env('PMS_DEFAULT_PROJECT_ID'),

    'timeout' => (int) env('PMS_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Webhook Replay Window
    |--------------------------------------------------------------------------
    |
    | Inbound PMS webhooks older than this many seconds are rejected.
    |
    */

    'webhook_tolerance_seconds' => 300,

];

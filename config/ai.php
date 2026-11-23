<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ticket Triage
    |--------------------------------------------------------------------------
    |
    | Classifies new tickets with an OpenAI-compatible chat completions API
    | (NVIDIA NIM or OpenRouter). Triage is off until AI_TRIAGE_ENABLED is
    | true and a model and API key are configured.
    |
    */

    'triage' => [
        'enabled' => env('AI_TRIAGE_ENABLED', false),
        'base_url' => env('AI_TRIAGE_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        'api_key' => env('AI_TRIAGE_API_KEY'),
        'model' => env('AI_TRIAGE_MODEL'),
        'timeout' => (int) env('AI_TRIAGE_TIMEOUT', 30),
        'min_confidence' => (float) env('AI_TRIAGE_MIN_CONFIDENCE', 0.5),
        'auto_critical_min_confidence' => (float) env('AI_TRIAGE_AUTO_CRITICAL_MIN_CONFIDENCE', 0.85),
    ],

];

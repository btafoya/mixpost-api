<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for all Mixpost API routes.
    |
    */
    'prefix' => 'api/mixpost',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests.
    |
    */
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => env('MIXPOST_API_RATE_LIMIT', 60),
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API token behavior.
    |
    */
    'token' => [
        'expiration' => env('MIXPOST_API_TOKEN_EXPIRATION'),
        'abilities_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Configure default pagination settings.
    |
    */
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Configure security settings for the API.
    |
    */
    'security' => [
        'ip_whitelist_enabled' => false,
        'ip_whitelist' => [],
        'https_only' => env('MIXPOST_API_HTTPS_ONLY', env('APP_ENV') === 'production'),
    ],
];

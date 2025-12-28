<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default WhatsApp Messages
    |--------------------------------------------------------------------------
    |
    | These are the default WhatsApp messages used throughout the CRM
    | when no custom template is selected. You can override these in .env
    |
    */

    'default_wa_message' => env(
        'DEFAULT_WA_MESSAGE',
        'Hi! Terima kasih sudah mampir ke booth kami. Berikut price list spesial pameran untuk kakak.'
    ),

    'default_wa_greeting' => env(
        'DEFAULT_WA_GREETING',
        'Halo! Terima kasih telah menghubungi kami.'
    ),

    'default_wa_followup' => env(
        'DEFAULT_WA_FOLLOWUP',
        'Halo! Ini follow-up dari kami. Apakah ada yang bisa kami bantu?'
    ),

    /*
    |--------------------------------------------------------------------------
    | Lead Scoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the scoring weights for lead qualification
    |
    */

    'lead_scoring' => [
        'decision_maker' => 15,
        'has_budget' => 15,
        'request_demo' => 10,
        'request_quotation' => 20,
        'couple_parents' => 30,
        'couple' => 15,
        'urgent_timeline' => 25,
        'this_year_timeline' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for pricing and other data
    |
    */

    'cache' => [
        'pricing_ttl' => env('PRICING_CACHE_TTL', 3600), // 1 hour
        'packages_limit' => env('PACKAGES_DISPLAY_LIMIT', 50),
        'addons_limit' => env('ADDONS_DISPLAY_LIMIT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure what gets logged in error scenarios
    |
    */

    'logging' => [
        'log_user_data' => env('LOG_USER_DATA_ON_ERROR', true),
        'log_stack_trace' => env('LOG_STACK_TRACE', true),
        'log_ip_address' => env('LOG_IP_ADDRESS', true),
    ],
];

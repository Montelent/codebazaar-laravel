<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product license verification
    |--------------------------------------------------------------------------
    |
    | Buyer installs call the seller license server only.
    | JigSource's /api/purchases/validation requires an "api_key" field on
    | every request — that is a product client key for this item, not the
    | buyer's secret. Prefer rotating it if the package is leaked.
    |
    | Envato personal tokens are NEVER shipped. Envato codes should be
    | verified on the license server (jigsource) when possible.
    |
    */
    'license' => [
        'verify_url' => env('LICENSE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation'),
        'product' => env('LICENSE_PRODUCT', 'codebazaar'),
        'item_id' => env('LICENSE_ITEM_ID'),
        'client_id' => env('LICENSE_CLIENT_ID'),
        // Required by jigsource.store validation API (body field: api_key)
        'api_key' => env('LICENSE_API_KEY', env('JIGSOURCE_API_KEY', 'sz34jtCB2mvA6zc8ESRUfUhp7ctlVcNNSCJ12Cza3S0F15BAlo')),
        'disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
    ],

    /*
    | Optional direct Envato (author demo server .env only — empty in zip)
    */
    'envato' => [
        'token' => env('ENVATO_PERSONAL_TOKEN'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
        'require_live' => (bool) env('ENVATO_REQUIRE_LIVE', true),
    ],

    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', env('LICENSE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation')),
        'api_key' => env('JIGSOURCE_API_KEY', env('LICENSE_API_KEY', 'sz34jtCB2mvA6zc8ESRUfUhp7ctlVcNNSCJ12Cza3S0F15BAlo')),
        'item_id' => env('JIGSOURCE_ITEM_ID', env('LICENSE_ITEM_ID')),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
        'product_slug' => env('JIGSOURCE_PRODUCT', env('LICENSE_PRODUCT', 'codebazaar')),
    ],
];

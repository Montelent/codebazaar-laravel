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
    | Secrets (API keys, Envato tokens) must come from environment variables
    | only. Never commit real values to this file.
    |
    | JigSource item: 1229 (CodeBazaar)
    |
    */
    'license' => [
        'verify_url' => env('LICENSE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation'),
        'product' => env('LICENSE_PRODUCT', 'codebazaar'),
        'item_id' => env('LICENSE_ITEM_ID', '1229'),
        'client_id' => env('LICENSE_CLIENT_ID'),
        'api_key' => env('LICENSE_API_KEY', env('JIGSOURCE_API_KEY')),
        'disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
    ],

    'envato' => [
        'token' => env('ENVATO_PERSONAL_TOKEN'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
        'require_live' => (bool) env('ENVATO_REQUIRE_LIVE', true),
    ],

    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', env('LICENSE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation')),
        'api_key' => env('JIGSOURCE_API_KEY', env('LICENSE_API_KEY')),
        'item_id' => env('JIGSOURCE_ITEM_ID', env('LICENSE_ITEM_ID', '1229')),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
        'product_slug' => env('JIGSOURCE_PRODUCT', env('LICENSE_PRODUCT', 'codebazaar')),
    ],
];

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
    | Envato / CodeCanyon purchase-code verification (author personal token required for live checks)
    | Get token: https://build.envato.com/create-token/
    | Scopes needed: View and search Envato sites, View your sales
    */
    'envato' => [
        'token' => env('ENVATO_PERSONAL_TOKEN'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
        // When true, UUID codes are rejected unless Envato API confirms the sale
        'require_live' => (bool) env('ENVATO_REQUIRE_LIVE', true),
    ],

    /*
    | JigSource.store license verification (live)
    | Default endpoint: https://jigsource.store/api/purchases/validation
    */
    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation'),
        'api_key' => env('JIGSOURCE_API_KEY'),
        'item_id' => env('JIGSOURCE_ITEM_ID'),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
        'product_slug' => env('JIGSOURCE_PRODUCT', 'codebazaar'),
    ],
];

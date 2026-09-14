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
    | Envato / CodeCanyon purchase-code verification (live)
    |--------------------------------------------------------------------------
    |
    | Endpoint: GET https://api.envato.com/v3/market/author/sale?code={purchase_code}
    | Auth:    Authorization: Bearer {personal_token}
    | Docs:    https://build.envato.com/api/#market_0_getAuthorSale
    | Token:   https://build.envato.com/create-token/
    | Scopes:  "View and search Envato sites" + "View your sales"
    |
    | ENVATO_PERSONAL_TOKEN in .env overrides the built-in author token.
    | ENVATO_ITEM_ID (optional) rejects codes for other CodeCanyon items.
    |
    */
    'envato' => [
        // Prefer .env; fallback keeps live verification working on fresh installs
        // without requiring buyers to set seller secrets in their .env.
        'token' => env('ENVATO_PERSONAL_TOKEN', 'ug7EGOJQw2nK2MYh7HPYvLLHsmQssFHA'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
        // When true, UUID codes must pass the Envato API (no format-only unlock)
        'require_live' => (bool) env('ENVATO_REQUIRE_LIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | JigSource.store license verification (unchanged)
    |--------------------------------------------------------------------------
    */
    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation'),
        'api_key' => env('JIGSOURCE_API_KEY'),
        'item_id' => env('JIGSOURCE_ITEM_ID'),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
        'product_slug' => env('JIGSOURCE_PRODUCT', 'codebazaar'),
    ],
];

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
    |
    */
    'envato' => [
        'token' => env('ENVATO_PERSONAL_TOKEN', 'ug7EGOJQw2nK2MYh7HPYvLLHsmQssFHA'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
        'require_live' => (bool) env('ENVATO_REQUIRE_LIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | JigSource.store purchase validation (live)
    |--------------------------------------------------------------------------
    |
    | Endpoint: POST https://jigsource.store/api/purchases/validation
    | Success:  { "status": "success", "data": { "purchase": { ... } } }
    | Error:    { "status": "error", "msg": "Invalid purchase code" }
    |
    */
    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', 'https://jigsource.store/api/purchases/validation'),
        'api_key' => env('JIGSOURCE_API_KEY', 'sz34jtCB2mvA6zc8ESRUfUhp7ctlVcNNSCJ12Cza3S0F15BAlo'),
        'item_id' => env('JIGSOURCE_ITEM_ID'),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
        'product_slug' => env('JIGSOURCE_PRODUCT', 'codebazaar'),
    ],
];

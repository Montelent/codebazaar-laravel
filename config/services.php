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

    'envato' => [
        'token' => env('ENVATO_PERSONAL_TOKEN'),
        'item_id' => env('ENVATO_ITEM_ID'),
        'licensing_disabled' => (bool) env('DISABLE_PRODUCT_LICENSE', false),
    ],

    /*
    | JigSource.store license verification
    | - verify_url: POST endpoint on your store that validates a license key
    | - api_key: optional shared secret sent as Bearer / X-Api-Key
    | - item_id: product id on jigsource for this script
    | - license_secret: HMAC secret to issue/verify JS1.*.* signed keys offline
    */
    'jigsource' => [
        'verify_url' => env('JIGSOURCE_VERIFY_URL', 'https://jigsource.store/api/license/verify'),
        'api_key' => env('JIGSOURCE_API_KEY'),
        'item_id' => env('JIGSOURCE_ITEM_ID'),
        'license_secret' => env('JIGSOURCE_LICENSE_SECRET'),
    ],
];

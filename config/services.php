<?php

return [

    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend'   => ['key' => env('RESEND_API_KEY')],
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token'   => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'                => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-3-5-haiku-20241022'),
    ],

    'whatsapp' => [
        'token'           => env('WHATSAPP_TOKEN'),
        'verify_token'    => env('WHATSAPP_VERIFY_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paynow' => [
        'integration_id'  => env('PAYNOW_INTEGRATION_ID'),
        'integration_key' => env('PAYNOW_INTEGRATION_KEY'),
        'result_url'      => env('PAYNOW_RESULT_URL', env('APP_URL').'/payments/webhook/paynow'),
        'return_url'      => env('PAYNOW_RETURN_URL', env('APP_URL').'/payments/success'),
    ],

    'ecocash' => [
        'merchant_code' => env('ECOCASH_MERCHANT_CODE'),
        'merchant_pin'  => env('ECOCASH_MERCHANT_PIN'),
        'base_url'      => env('ECOCASH_BASE_URL', 'https://api.ecocash.co.zw/v1'),
    ],

    'innbucks' => [
        'api_key'     => env('INNBUCKS_API_KEY'),
        'merchant_id' => env('INNBUCKS_MERCHANT_ID'),
    ],

    'zoom' => [
        'client_id'     => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'account_id'    => env('ZOOM_ACCOUNT_ID'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

];

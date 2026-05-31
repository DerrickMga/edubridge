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
        'endpoint'    => env('ANTHROPIC_ENDPOINT', 'https://info-5426-resource.openai.azure.com/openai/v1'),
        'api_key'     => env('ANTHROPIC_API_KEY'),
        'model'       => env('ANTHROPIC_MODEL', 'claude-opus-4-7'),
        'api_version' => env('ANTHROPIC_API_VERSION', '2024-10-21'),
    ],

    'azure_ai' => [
        'endpoint'    => env('AZURE_AI_ENDPOINT'),
        'key'         => env('AZURE_AI_KEY'),
        'api_version' => env('AZURE_AI_API_VERSION', '2025-01-01'),
    ],

    'openai' => [
        'api_key'          => env('OPENAI_API_KEY'),
        'companion_model'  => env('OPENAI_COMPANION_MODEL', 'gpt-4o'),
        'base_url'         => 'https://api.openai.com/v1',
    ],

    'gemini' => [
        // API key from Google AI Studio: https://aistudio.google.com/app/apikey
        // (NOT the same as GOOGLE_API_KEY — that is for YouTube/Calendar APIs)
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
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

    'payfast' => [
        'merchant_id'  => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'passphrase'   => env('PAYFAST_PASSPHRASE', ''),
        'test_mode'    => env('PAYFAST_TEST_MODE', false),
        'return_url'   => env('PAYFAST_RETURN_URL', env('APP_URL').'/payments/success'),
        'cancel_url'   => env('PAYFAST_CANCEL_URL', env('APP_URL').'/payments/cancel'),
        'notify_url'   => env('PAYFAST_NOTIFY_URL', env('APP_URL').'/payments/webhook/payfast'),
        'usd_zar_rate' => env('PAYFAST_USD_ZAR_RATE', 18.5),
    ],

    'zoom' => [
        'client_id'            => env('ZOOM_CLIENT_ID'),
        'client_secret'        => env('ZOOM_CLIENT_SECRET'),
        'account_id'           => env('ZOOM_ACCOUNT_ID'),
        'webhook_secret_token' => env('ZOOM_WEBHOOK_SECRET_TOKEN'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
        'api_key'       => env('GOOGLE_API_KEY'),
    ],

];

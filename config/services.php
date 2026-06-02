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

    // Groq Cloud — free tier, ultra-fast inference (Llama 3.3, Gemma 2, Mixtral)
    // Get free API key: https://console.groq.com
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model'   => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    // DeepSeek — excellent at STEM/maths/reasoning, very affordable
    // Get API key: https://platform.deepseek.com
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'model'   => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    'whatsapp' => [
        'app_id'              => env('WHATSAPP_APP_ID'),
        'app_secret'          => env('WHATSAPP_APP_SECRET'),
        'token'               => env('WHATSAPP_TOKEN'),
        'verify_token'        => env('WHATSAPP_VERIFY_TOKEN'),
        'phone_number_id'     => env('WHATSAPP_PHONE_NUMBER_ID'),
        'waba_id'             => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'otp_template'        => env('WHATSAPP_OTP_TEMPLATE', 'otp_verification'),
        'flow_private_key'    => env('WHATSAPP_FLOW_PRIVATE_KEY'),
        'register_flow_id'    => env('WHATSAPP_REGISTER_FLOW_ID'),
        'student_hub_flow_id' => env('WHATSAPP_STUDENT_HUB_FLOW_ID'),
        'teacher_hub_flow_id' => env('WHATSAPP_TEACHER_HUB_FLOW_ID'),
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

    // Paystack — SA bank EFT and card payouts (used for teacher settlements to SA)
    // Keys shared from VitalBot (same WABA/entity).
    'paystack' => [
        'public_key'  => env('PAYSTACK_PUBLIC_KEY', ''),
        'secret_key'  => env('PAYSTACK_SECRET_KEY', ''),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'callback_url'=> env('PAYSTACK_CALLBACK_URL', ''),
    ],

    // O'mari (Old Mutual ZW) — wallet top-up / cash-in / cash-out payout
    // Lowest settlement fee (1.5%). Keys shared from VitalBot.
    'omari' => [
        'environment'    => env('OMARI_ENVIRONMENT', 'sandbox'),
        'sandbox_url'    => env('OMARI_SANDBOX_URL', 'https://omari.v.co.zw/uat/vsuite/omari/api/merchant/api/payment'),
        'production_url' => env('OMARI_PRODUCTION_URL', 'https://omari.v.co.zw/vsuite/omari/api/merchant/api/payment'),
        'base_url'       => env('OMARI_BASE_URL', ''),
        'api_key'        => env('OMARI_API_KEY', ''),
        'merchant_code'  => env('OMARI_MERCHANT_CODE', ''),
        'channel'        => env('OMARI_CHANNEL', 'WEB'),
        'timeout'        => (int) env('OMARI_TIMEOUT', 30),
    ],

    // O'mari Agent REST API — cash-in (credit a customer wallet), cash-out voucher redemption
    'omari_agent' => [
        'environment'    => env('OMARI_ENVIRONMENT', 'sandbox'),
        'sandbox_url'    => env('OMARI_AGENT_SANDBOX_URL', 'https://omari.v.co.zw/uat/vsuite/omari/api/agent'),
        'production_url' => env('OMARI_AGENT_PRODUCTION_URL', 'https://omari.v.co.zw/vsuite/omari/api/agent'),
        'base_url'       => env('OMARI_AGENT_BASE_URL', ''),
        'api_key'        => env('OMARI_AGENT_API_KEY', ''),
        'timeout'        => (int) env('OMARI_TIMEOUT', 30),
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

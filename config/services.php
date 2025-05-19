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

    'whatsapp' => [
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'token' => env('WHATSAPP_TOKEN'),
        'template' => env('WHATSAPP_TEMPLATE_NAME', 'cotizacion'),
        'public_url' => env('WHATSAPP_PUBLIC_BASE_URL', 'https://cotizador.espumasmedellin.com'),
        'version' => env('WHATSAPP_API_VERSION', 'v22.2'),
    ],

    'whatsapp_litoral' => [
        'phone_id' => env('WHATSAPP_PHONE_ID_LITORAL'),
        'token' => env('WHATSAPP_TOKEN'), // 👉 mismo token
        'template' => env('WHATSAPP_TEMPLATE_LITORAL', 'cotizacionx'),
        'public_url' => env('WHATSAPP_PUBLIC_BASE_URL', 'https://cotizador.espumasmedellin.com'),
        'version' => env('WHATSAPP_API_VERSION', 'v22.2'),
    ],

];

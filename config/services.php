<?php

// Konfigurasi layanan pihak ketiga.
// Nilai rahasia (token, key) selalu dibaca dari .env lewat env(), tidak ditulis langsung di sini.
return [

    // Layanan email bawaan Laravel (tidak dipakai di aplikasi ini, dibiarkan sesuai bawaan)
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xendit' => [
        'secret' => env('XENDIT_SECRET_KEY'),
        'url' => env('XENDIT_URL', 'https://api.xendit.co'),
    ],

];
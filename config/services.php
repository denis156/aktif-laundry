<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Google OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Google OAuth 2.0 Social Login
    | Digunakan untuk login pelanggan menggunakan akun Google mereka
    |
    | Setup:
    | 1. Buka Google Cloud Console: https://console.cloud.google.com/
    | 2. Buat project baru atau pilih project yang ada
    | 3. Enable Google+ API atau People API
    | 4. Buat OAuth 2.0 credentials (Web Application)
    | 5. Tambahkan Authorized redirect URIs di Google Console
    | 6. Copy Client ID dan Client Secret ke .env file
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => config('app.url').'/pelanggan/auth/google/callback',
    ],

];

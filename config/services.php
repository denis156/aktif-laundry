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

    /*
    |--------------------------------------------------------------------------
    | Fonnte API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Fonnte WhatsApp API
    | Digunakan untuk mengirim pesan WhatsApp, mengelola device, dan aktivasi QR
    |
    | Setup:
    | 1. Daftar akun di: https://fonnte.com/
    | 2. Dapatkan API token dari dashboard Fonnte
    | 3. Copy token ke .env file sebagai FONNTE_TOKEN
    | 4. Gunakan helper app/Helper/FonnteHelper.php untuk integrasi
    |
    | Fitur yang tersedia:
    | - Kirim pesan WhatsApp melalui device yang terdaftar
    | - Tambah dan hapus device WhatsApp
    | - Aktivasi device dengan QR code
    | - Monitor status device
    | - Kirim pesan dengan format media (image, document, dll)
    |
    */

    'fonnte' => [
        'fonnte_token' => env('FONNTE_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapbox Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Mapbox Maps API
    | Digunakan untuk rendering maps dengan Mapbox GL JS
    |
    | Setup:
    | 1. Daftar akun di: https://www.mapbox.com/
    | 2. Dapatkan access token dari dashboard Mapbox
    | 3. Copy token ke .env file sebagai MAPBOX_TOKEN
    |
    | Fitur yang tersedia:
    | - Vector maps dengan performa tinggi
    | - Custom map styles
    | - 3D terrain dan buildings
    | - Realtime tracking dengan smooth animations
    | - Offline map support
    |
    | Fallback: Sistem akan otomatis fallback ke Leaflet jika Mapbox tidak tersedia
    |
    */

    'mapbox' => [
        'token' => env('MAPBOX_TOKEN'),
    ],

];

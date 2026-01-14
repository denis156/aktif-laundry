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
    | 2. Dapatkan API token dari dashboard Fonnte (Account Token)
    | 3. Dapatkan Device Token dari device yang sudah ditambahkan
    | 4. Copy tokens ke .env file sebagai FONNTE_TOKEN dan FONNTE_DEVICE_TOKEN
    | 5. Gunakan service app/Services/FonnteService.php untuk integrasi
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
        'device_token' => env('FONNTE_DEVICE_TOKEN'),
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

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Firebase Cloud Messaging (FCM) dan Firebase Services
    | Digunakan untuk push notifications ke web, iOS, dan Android
    |
    | Setup:
    | 1. Buka Firebase Console: https://console.firebase.google.com/
    | 2. Buat atau pilih project yang ada
    | 3. Download Firebase Admin SDK credentials (JSON file)
    | 4. Simpan ke storage/app/firebase/
    | 5. Di Project Settings > Cloud Messaging, dapatkan VAPID Key
    | 6. Copy semua credentials ke .env file
    |
    | Fitur yang tersedia:
    | - Push notifications untuk web (PWA)
    | - Push notifications untuk mobile apps
    | - Multi-device token management
    | - Firebase Realtime Database
    | - Firebase Authentication
    |
    */

    'firebase' => [
        // Admin SDK credentials for backend/server
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'vapid_key' => env('FIREBASE_VAPID_KEY'),

        // Web/PWA configuration for frontend
        'web' => [
            'api_key' => env('FIREBASE_API_KEY'),
            'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
            'database_url' => env('FIREBASE_DATABASE_URL'),
            'project_id' => env('FIREBASE_PROJECT_ID'),
            'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
            'app_id' => env('FIREBASE_APP_ID'),
            'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Z.AI Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Z.AI API (Anthropic Compatible)
    | Digunakan untuk AI Chatbot WhatsApp yang menjawab pertanyaan pelanggan
    |
    | Setup:
    | 1. Daftar akun di: https://z.ai/
    | 2. Dapatkan API key dari dashboard Z.AI
    | 3. Copy API key ke .env file sebagai ZAI_API_KEY
    |
    | Fitur yang tersedia:
    | - AI chatbot dengan model GLM-4.6 flagship
    | - Response natural dan kontekstual
    | - Support Anthropic format untuk compatibility
    | - Konfigurasi temperature, max tokens, dan timeout
    |
    */

    'zai' => [
        'api_key' => env('ZAI_API_KEY'),
        'model' => env('ZAI_MODEL', 'glm-4.6'),
        'max_tokens' => env('ZAI_MAX_TOKENS', 65536),
        'temperature' => env('ZAI_TEMPERATURE', 0.7),
        'timeout' => env('ZAI_TIMEOUT', 25),
        'api_url' => 'https://api.z.ai/api/anthropic/v1/messages',
    ],

];

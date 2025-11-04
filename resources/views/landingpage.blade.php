<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} – Tetap Aktif, Tetap Bersih</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-dvh w-full bg-linear-to-bl from-primary/38 via-primary/68 to-primary/28 font-sans antialiased flex items-center justify-center p-4">
    <div class="space-y-6 text-center">
        <!-- Logo with subtle floating animation -->
        <div class="flex justify-center">
            <img src="{{ asset('images/Logo.png') }}" alt="Aktif Laundry"
                class="h-28 sm:h-32 md:h-36 lg:h-40 w-auto drop-shadow-lg rounded-lg p-2 floating" />
        </div>

        <!-- Main tagline with better typography hierarchy -->
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-primary-content">
            Tetap Aktif, Tetap Bersih
        </h1>

        <!-- Description with improved spacing and tone -->
        <p class="text-sm sm:text-base md:text-lg text-primary-content/80 leading-relaxed">
            Layanan laundry kami sudah aktif!<br>
            Website sedang kami perbaiki dengan cinta.<br>
            <span class="font-bold text-success">Yuk langsung pesan via WhatsApp — cepat & mudah!</span>
        </p>

        <!-- WhatsApp CTA Button (more prominent) -->
        <div class="pt-4">
            <x-button
                label="Pesan Sekarang via WhatsApp"
                icon="o-chat-bubble-bottom-center-text"
                link="https://wa.me/6282156912202?text=Hai%20Admin%20Aktif%20Laundry%2C%20saya%20mau%20nyuci%20nih!"
                external
                class="btn-success btn-block btn-md md:btn-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200" />
        </div>

        <!-- Subtle, friendly note at the bottom -->
        <p class="text-xs text-primary-content/70 mt-6">
            *Website sedang dalam pengembangan. Terima kasih atas kesabaran dan dukunganmu!
        </p>
    </div>
</body>

</html>

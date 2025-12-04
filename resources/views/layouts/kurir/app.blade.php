<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#cf4040">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ route('manifest.kurir') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Kurir Aktif">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">

    {{-- Livewire Style --}}
    @livewireStyles

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Lefalet.js --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>

<body class="min-h-dvh min-w-dvw font-sans antialiased bg-base-200">

    <livewire:kurir.component.top-nav />

    <main class="h-full w-full p-4">
        {{ $slot }}
    </main>

    <livewire:kurir.component.bottom-nav />

    {{-- TOAST area --}}
    <x-toast />

    {{-- LOADING INDICATOR --}}
    <livewire:component.loading />

    {{-- FAB DOWNLOAD --}}
    <livewire:component.fab-kurir />

    {{-- Livewire Script --}}
    @livewireScripts

    {{-- Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    await navigator.serviceWorker.register('/sw-kurir.js', {
                        scope: '/kurir/'
                    });
                } catch (error) {
                    console.error('[SW Kurir] Registration failed:', error);
                }
            });
        }
    </script>
</body>

</html>

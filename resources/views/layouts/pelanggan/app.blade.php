<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta name="theme-color" content="#cf4040">
    <meta name="mapbox-token" content="{{ config('services.mapbox.token') }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ route('manifest.pelanggan') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aktif Laundry">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    {{-- iOS Splash Screens --}}
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/640x1136.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/750x1294.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/1242x2148.png') }}" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/1125x2436.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/414x816.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/1536x2048.png') }}" media="(min-device-width: 768px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/1668x2224.png') }}" media="(min-device-width: 834px) and (max-device-width: 834px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash-screen/2048x2732.png') }}" media="(min-device-width: 1024px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)">

    {{-- Livewire Style --}}
    @livewireStyles

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-dvh min-w-dvw font-sans antialiased bg-base-200">

    <livewire:pelanggan.components.top-nav />

    <main class="h-full w-full p-4">
        {{ $slot }}
    </main>

    <livewire:pelanggan.components.bottom-nav />

    {{-- TOAST area --}}
    <x-toast />

    {{-- LOADING INDICATOR --}}
    <livewire:components.loading />

    {{-- FAB DOWNLOAD --}}
    <livewire:components.fab-pelanggan />

    {{-- Livewire Script --}}
    @livewireScripts

    {{-- Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    await navigator.serviceWorker.register('/sw-pelanggan.js', {
                        scope: '/pelanggan/'
                    });
                } catch (error) {
                    console.error('[SW Pelanggan] Registration failed:', error);
                }
            });
        }
    </script>
</body>

</html>

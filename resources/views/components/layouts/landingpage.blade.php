<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Vanilla Calendar --}}
    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.css" rel="stylesheet">
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapse-text="Tutup" right-mobile collapsible class="bg-base-100 lg:bg-base-100 rounded-l-xl rounded-r-none md:rounded-l-none md:rounded-r-2xl lg:rounded-l-none lg:rounded-r-4xl">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />

            {{-- MENU --}}
            <x-menu activate-by-route active-bg-color="font-black bg-primary text-primary-content hover:text-primary-content">

                <x-menu-separator />

                <!-- Menu Hero Section Only -->
                <x-menu-item title="Beranda" icon="o-home" link="#" exact />
                <x-menu-item title="Layanan" icon="o-sparkles" link="#" exact />
                <x-menu-item title="Tentang" icon="o-information-circle" link="#" exact />
                <x-menu-item title="Kontak" icon="o-phone" link="#" exact />

                <x-menu-separator />

                <!-- Quick Actions -->
                <x-menu-item title="Pesan via WhatsApp" icon="o-chat-bubble-bottom-center-text"
                    link="https://wa.me/6282156912202?text=Hai%20Admin%20Aktif%20Laundry%2C%20saya%20mau%20nyuci%20nih!"
                    external />

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>

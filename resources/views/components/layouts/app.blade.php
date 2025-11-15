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

                {{-- User Info --}}
                @if($user = Auth::user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 -my-2! rounded">
                        <x-slot:actions>
                            <x-theme-toggle class="btn btn-circle btn-secondary btn-md" />
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" wire:navigate.hover exact />

                <x-menu-separator />

                <x-menu-item title="Kasir" icon="o-calculator" link="{{ route('kasir') }}" wire:navigate.hover exact />
                <x-menu-item title="Transaksi" icon="o-clipboard-document-list" link="{{ route('transaksi.index') }}" wire:navigate.hover exact />

                <x-menu-separator />

                <x-menu-item title="Layanan" icon="o-sparkles" link="{{ route('layanan.index') }}" wire:navigate.hover exact />
                <x-menu-item title="Pelanggan" icon="o-users" link="{{ route('pelanggan.index') }}" wire:navigate.hover exact />
                <x-menu-item title="Jenis Pakaian" icon="o-square-3-stack-3d" link="{{ route('jenis-pakaian.index') }}" wire:navigate.hover exact />

                <x-menu-separator />

                <x-menu-item title="Admin" icon="o-user-group" link="{{ route('admin.index') }}" wire:navigate.hover exact />
                <x-menu-item title="Pengaturan" icon="o-cog-6-tooth" link="{{ route('pengaturan') }}" wire:navigate.hover exact />
                <x-menu-item title="Profil Saya" icon="o-user-circle" link="{{ route('profile') }}" wire:navigate.hover exact />

                <x-menu-separator />

                <x-menu-item title="Keluar" icon="o-arrow-right-start-on-rectangle" no-wire-navigate class="text-error" link="{{ route('logout') }}" />

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

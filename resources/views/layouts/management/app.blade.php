<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    {{-- Livewire Style --}}
    @livewireStyles

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="text/javascript"
        src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Vanilla Calendar --}}
    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.css"
        rel="stylesheet">

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>
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
        <x-slot:sidebar drawer="main-drawer" collapse-text="Tutup" right-mobile collapsible
            class="bg-base-100 lg:bg-base-100 border-r border-primary rounded-l-xl rounded-r-none md:rounded-l-none md:rounded-r-2xl lg:rounded-l-none lg:rounded-r-4xl">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />

            {{-- MENU --}}
            <x-menu activate-by-route
                active-bg-color="font-black bg-primary text-primary-content hover:text-primary-content">

                {{-- User Info --}}
                @if($user = Auth::user())
                <x-menu-separator />

                @php
                $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder($user->avatar_url, $user->name);
                @endphp
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                    class="-mx-2 -my-2! rounded">
                    <x-slot:avatar>
                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}"
                            class="w-11 h-11 rounded-full object-cover avatar-online" />
                    </x-slot:avatar>
                    <x-slot:actions>
                        <livewire:component.dark-mode-swap swap-class="swap-rotate" icon-size="h-6 w-6" />
                    </x-slot:actions>
                </x-list-item>

                <x-menu-separator />
                @endif

                {{-- Dashboard --}}
                <x-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" wire:navigate.hover
                    exact />

                <x-menu-separator />

                {{-- Operasional --}}
                <x-menu-sub title="Operasional" icon="o-clipboard-document-check">
                    <x-menu-item title="Kasir" icon="o-calculator" link="{{ route('kasir') }}" wire:navigate.hover />
                    <x-menu-item title="Transaksi" icon="o-clipboard-document-list"
                        link="{{ route('transaksi.index') }}" wire:navigate.hover />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Master Data --}}
                <x-menu-sub title="Master Data" icon="o-folder-open">
                    <x-menu-item title="Pelanggan" icon="o-users" link="{{ route('pelanggan.index') }}"
                        wire:navigate.hover />
                    <x-menu-item title="Layanan" icon="o-sparkles" link="{{ route('layanan.index') }}"
                        wire:navigate.hover />
                    <x-menu-item title="Jenis Pakaian" icon="o-square-3-stack-3d"
                        link="{{ route('jenis-pakaian.index') }}" wire:navigate.hover />
                    <x-menu-item title="Kurir" icon="o-truck" link="{{ route('kurir.index') }}" wire:navigate.hover />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Marketing --}}
                <x-menu-sub title="Marketing" icon="o-megaphone">
                    <x-menu-item title="Promo" icon="o-tag" link="{{ route('promo.index') }}" wire:navigate.hover />
                    <x-menu-item title="Referral" icon="o-gift" link="{{ route('referral.index') }}"
                        wire:navigate.hover />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Pengaturan (Super Admin only) --}}
                @if(Auth::user()->super_admin)
                <x-menu-sub title="Pengaturan" icon="o-cog-6-tooth">
                    <x-menu-item title="Staf" icon="o-user-group" link="{{ route('staf.index') }}"
                        wire:navigate.hover />
                    <x-menu-item title="Konfigurasi" icon="o-adjustments-horizontal" link="{{ route('pengaturan') }}"
                        wire:navigate.hover />
                </x-menu-sub>
                <x-menu-separator />
                @endif

                {{-- Profil --}}
                <x-menu-item title="Profil Saya" icon="o-user-circle" link="{{ route('profile') }}"
                    wire:navigate.hover />

                <x-menu-separator />

                <x-menu-item title="Keluar" icon="o-arrow-right-start-on-rectangle" no-wire-navigate class="text-error"
                    link="{{ route('logout') }}" />

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- TOAST area --}}
    <x-toast />

    {{-- Livewire Script --}}
    @livewireScripts
</body>

</html>

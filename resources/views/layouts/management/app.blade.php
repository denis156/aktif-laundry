<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta name="mapbox-token" content="{{ config('services.mapbox.token') }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">

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
                        <livewire:components.dark-mode-swap swap-class="swap-rotate" icon-size="h-6 w-6" />
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
                    <x-menu-item title="Lacak Kurir" icon="o-map-pin" link="{{ route('tracking.index') }}"
                        wire:navigate.hover />
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
                    <x-menu-item title="Fonnte WhatsApp" icon="o-chat-bubble-bottom-center-text" link="{{ route('fonnte.index') }}"
                        wire:navigate.hover />
                    <x-menu-item title="Konfigurasi" icon="o-adjustments-horizontal" link="{{ route('pengaturan') }}"
                        wire:navigate.hover />
                </x-menu-sub>
                <x-menu-separator />
                @endif

                {{-- Akun --}}
                <x-menu-sub title="Akun" icon="o-user-circle">
                    <x-menu-item title="Profil Saya" icon="o-user" link="{{ route('profile') }}"
                        wire:navigate.hover />
                    <x-menu-item title="Chat" icon="o-chat-bubble-left-right" link="{{ route('chat.index') }}"
                        wire:navigate.hover />
                </x-menu-sub>

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

    {{-- Firebase Cloud Messaging --}}
    @if(Auth::check())
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.web.api_key') }}",
            authDomain: "{{ config('services.firebase.web.auth_domain') }}",
            databaseURL: "{{ config('services.firebase.web.database_url') }}",
            projectId: "{{ config('services.firebase.web.project_id') }}",
            storageBucket: "{{ config('services.firebase.web.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.web.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.web.app_id') }}",
            measurementId: "{{ config('services.firebase.web.measurement_id') }}"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        // Function to initialize Firebase Messaging
        async function initFirebaseMessaging() {
            try {
                // Request notification permission
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    console.log('Notification permission granted.');

                    // Register service worker
                    if ('serviceWorker' in navigator) {
                        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                        console.log('Service Worker registered:', registration);
                    }

                    // Get FCM token
                    const token = await getToken(messaging, {
                        vapidKey: "{{ config('services.firebase.vapid_key') }}"
                    });

                    if (token) {
                        console.log('FCM Token obtained:', token);

                        // Send token to server
                        await fetch("{{ route('fcm.token.update') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                fcm_token: token
                            })
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  console.log('FCM token saved successfully');
                              }
                          });
                    } else {
                        console.log('No registration token available.');
                    }
                } else if (permission === 'denied') {
                    console.log('Notification permission denied.');
                } else {
                    console.log('Notification permission dismissed.');
                }
            } catch (err) {
                console.error('Error initializing Firebase Messaging:', err);
            }
        }

        // Handle foreground messages
        onMessage(messaging, (payload) => {
            console.log('Message received in foreground:', payload);

            const notificationTitle = payload.notification?.title || 'Aktif Laundry';
            const notificationOptions = {
                body: payload.notification?.body || 'You have a new notification',
                icon: payload.notification?.icon || '/icons/icon-512x512.png',
                badge: '/icons/icon-512x512.png',
                data: payload.data || {}
            };

            // Show notification if browser supports it
            if (Notification.permission === 'granted') {
                new Notification(notificationTitle, notificationOptions);
            }
        });

        // Initialize on page load
        initFirebaseMessaging();
    </script>
    @endif
</body>

</html>

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
    <link rel="manifest" href="{{ route('manifest.kurir') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SiAktif">
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

    <livewire:kurir.components.top-nav />

    <main class="h-full w-full p-4">
        {{ $slot }}
    </main>

    <livewire:kurir.components.bottom-nav />

    {{-- TOAST area --}}
    <x-toast />

    {{-- LOADING INDICATOR --}}
    <livewire:components.loading />

    {{-- FAB DOWNLOAD --}}
    <livewire:components.fab-kurir />

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

    {{-- Firebase Cloud Messaging --}}
    @if(Auth::guard('kurir')->check())
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging.js";

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

                    // Wait for service worker to be ready
                    if ('serviceWorker' in navigator) {
                        // Register Firebase service worker separately
                        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                            scope: '/firebase-cloud-messaging-push-scope'
                        });

                        // Wait for service worker to be active
                        await navigator.serviceWorker.ready;
                        console.log('Service Worker ready for FCM');

                        // Get FCM token with the registration
                        const token = await getToken(messaging, {
                            vapidKey: "{{ config('services.firebase.vapid_key') }}",
                            serviceWorkerRegistration: registration
                        });

                        if (token) {
                            console.log('FCM Token obtained:', token);

                            // Send token to server
                            await fetch("{{ route('kurir.fcm.token.update') }}", {
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
                vibrate: [200, 100, 200], // Vibration pattern
                data: payload.data || {}
            };

            // Show notification if browser supports it
            if (Notification.permission === 'granted') {
                new Notification(notificationTitle, notificationOptions);
            }
        });

        // Wait a bit for page to fully load and other service workers to register
        window.addEventListener('load', () => {
            setTimeout(initFirebaseMessaging, 1000);
        });
    </script>
    @endif
</body>

</html>

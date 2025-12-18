<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#cf4040">

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ route('manifest.pelanggan') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aktif Laundry">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- SEO Meta Tags -->
    <title>{{ config('app.name') }} - Jasa Laundry Profesional Kendari | Cuci Setrika Rp 5.000/kg | Antar Jemput Gratis</title>
    <meta name="description"
        content="{{ config('app.name') }} - Jasa laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg, cuci setrika Rp 7.000/kg. Gratis antar jemput untuk mahasiswa dan pekerja sibuk. WhatsApp sekarang!">
    <meta name="keywords"
        content="laundry Kendari, laundry Sulawesi Tenggara, cuci baju Kendari, laundry kiloan Kendari, laundry mahasiswa Kendari, laundry antar jemput Kendari, cuci setrika Kendari, cuci lipat Kendari, laundry profesional Kendari, laundry murah Kendari, laundry express Kendari, {{ config('app.name') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name') }} - Jasa Laundry Profesional Kendari | Gratis Antar Jemput">
    <meta property="og:description"
        content="Laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg. Hemat waktu dan energi dengan layanan laundry antar jemput gratis. WhatsApp sekarang!">
    <meta property="og:image" content="{{ asset('images/Logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} - Jasa Laundry Profesional Kendari">
    <meta name="twitter:description"
        content="Laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg. Gratis antar jemput untuk mahasiswa dan pekerja sibuk.">
    <meta name="twitter:image" content="{{ asset('images/Logo.png') }}">

    <!-- Additional SEO -->
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="id">
    <meta name="geo.region" content="ID">
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Additional SEO Meta Tags -->
    <meta name="geo.placename" content="Kendari">
    <meta name="geo.region" content="ID-SU">
    <meta name="classification" content="Laundry Service">
    <meta name="category" content="Business, Services, Laundry">
    <meta name="coverage" content="Worldwide">
    <meta name="distribution" content="Global">
    <meta name="rating" content="General">

    <!-- Logo for SEO -->
    <link rel="image_src" href="{{ asset('images/Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Force light theme for landing page --}}
    <script>
        // Force light theme on landing page, ignore system preference and localStorage
        document.documentElement.setAttribute('data-theme', 'light');

        // Override after DOMContentLoaded
        document.addEventListener('DOMContentLoaded', () => {
            document.documentElement.setAttribute('data-theme', 'light');
        });

        // Override after Livewire navigated (if any)
        document.addEventListener('livewire:navigated', () => {
            document.documentElement.setAttribute('data-theme', 'light');
        });
    </script>
</head>

<body class="min-h-dvh antialiased bg-base-200">
    <div class="drawer drawer-end">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col min-h-dvh">

            <!-- Navbar -->
            <nav class="navbar bg-base-200 w-full sticky top-0 z-10 shadow-lg border-b border-accent border-dashed">
                <div class="flex-none lg:hidden">
                    <label for="my-drawer-2" aria-label="open sidebar" class="btn btn-square btn-ghost">
                        <x-icon name="o-bars-3" class="inline-block h-6 w-6 stroke-current" />
                    </label>
                </div>
                <div class="flex-1 px-2">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/Logo.png') }}" alt="{{ config('app.name') }}"
                            class="h-12 w-auto ml-auto md:ml-0">
                        <span class="font-bold text-lg text-primary hidden md:block">Tetap Aktif, Tetap Bersih</span>
                    </div>
                </div>
                <div class="hidden flex-none lg:block">
                    <ul class="menu menu-horizontal px-1 gap-2">
                        <!-- Navbar menu content here -->
                        <li><a href="#beranda" class="btn btn-ghost btn-primary">Beranda</a></li>
                        <li><a href="#smart-clean" class="btn btn-ghost btn-primary">Smart & Clean</a></li>
                        <li><a href="#pesan" class="btn btn-success">Pesan Sekarang</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Page content here -->
            <main class="flex-1">
                <livewire:landing-page.beranda />

                <livewire:landing-page.smart-clean />

                <livewire:landing-page.pesan />

                <livewire:components.fab-landing-page>
            </main>

            <!-- Footer Content -->
            <footer
                class="footer footer-horizontal footer-center bg-primary text-base-content rounded-t-2xl p-4 border-t-2 border-primary-content border-dashed">
                <!-- Company Info -->
                <nav class="grid grid-flow-col gap-4">
                    <a href="#beranda" class="link text-primary-content text-md">Beranda</a>
                    <a href="#smart-clean" class="link text-primary-content text-md">Smart & Clean</a>
                    <a href="#pesan" class="link text-primary-content text-md">Pesan Sekarang</a>
                </nav>

                <!-- Social Media -->
                <nav>
                    <div class="grid grid-flow-col gap-4">
                        <!-- TikTok -->
                        <a href="https://www.tiktok.com/@miegacoanlevel01" target="_blank"
                            class="btn btn-circle btn-primary-content btn-outline text-primary-content hover:text-base-content">
                            <x-icon name="bi.tiktok" class="w-4 h-4 fill-current" />
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/6282156912202" target="_blank"
                            class="btn btn-circle btn-primary-content btn-outline text-primary-content hover:text-base-content">
                            <x-icon name="bi.whatsapp" class="w-4 h-4 fill-current" />
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/aktif_laundry" target="_blank"
                            class="btn btn-circle btn-primary-content btn-outline text-primary-content hover:text-base-content">
                            <x-icon name="bi.instagram" class="w-4 h-4 fill-current" />
                        </a>
                    </div>
                </nav>

                <!-- Copyright -->
                <aside>
                    <div class="flex flex-col items-center gap-2">
                        <p class="font-semibold text-md text-primary-content">{{ config('app.name') }} ©
                            {{ date('Y') }}</p>
                        <p class="text-sm text-primary-content">Tetap Aktif, Tetap Bersih</p>
                    </div>
                </aside>
            </footer>
        </div>
        <aside class="drawer-side">
            <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-200 min-h-dvh w-[64dvw] p-4">
                <!-- Sidebar header dengan logo -->
                <div class="flex flex-col items-center p-4 mb-4 space-y-2">
                    <img src="{{ asset('images/Logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
                    <span class="font-bold text-sm text-primary text-center">Tetap Aktif, Tetap Bersih</span>
                </div>
                <div class="space-y-2">
                    <!-- Sidebar content here -->
                    <li><a href="#beranda" class="btn btn-sm btn-primary btn-ghost">Beranda</a></li>
                    <li><a href="#smart-clean" class="btn btn-sm btn-primary btn-ghost">Smart & Clean</a></li>
                    <li><a href="#pesan" class="btn btn-sm btn-success btn-block">Pesan Sekarang</a></li>
                </div>
            </ul>
        </aside>
    </div>

    {{-- TOAST area --}}
    <x-toast />

    @livewireScripts
</body>

</html>

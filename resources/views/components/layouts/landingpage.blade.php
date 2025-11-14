<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>Aktif Laundry - Jasa Laundry Profesional Kendari | Cuci Setrika Rp 5.000/kg | Antar Jemput Gratis</title>
    <meta name="description"
        content="Aktif Laundry - Jasa laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg, cuci setrika Rp 7.000/kg. Gratis antar jemput untuk mahasiswa dan pekerja sibuk. WhatsApp sekarang!">
    <meta name="keywords"
        content="laundry Kendari, laundry Sulawesi Tenggara, cuci baju Kendari, laundry kiloan Kendari, laundry mahasiswa Kendari, laundry antar jemput Kendari, cuci setrika Kendari, cuci lipat Kendari, laundry profesional Kendari, laundry murah Kendari, laundry express Kendari, Aktif Laundry">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Aktif Laundry - Jasa Laundry Profesional Kendari | Gratis Antar Jemput">
    <meta property="og:description"
        content="Laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg. Hemat waktu dan energi dengan layanan laundry antar jemput gratis. WhatsApp sekarang!">
    <meta property="og:image" content="{{ asset('images/Logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Aktif Laundry - Jasa Laundry Profesional Kendari">
    <meta name="twitter:description"
        content="Laundry profesional di Kendari, Sulawesi Tenggara. Cuci + lipat mulai Rp 5.000/kg. Gratis antar jemput untuk mahasiswa dan pekerja sibuk.">
    <meta name="twitter:image" content="{{ asset('images/Logo.png') }}">

    <!-- Additional SEO -->
    <meta name="author" content="Aktif Laundry">
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

    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ url('/images/Logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/images/Logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/images/Logo.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aktif Laundry">
    <meta name="application-name" content="Aktif Laundry">
    <meta name="msapplication-TileColor" content="#dc2626">

    <!-- Structured Data -->
    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Aktif Laundry',
            'alternateName' => 'Aktif Laundry Kendari',
            'description' =>
                'Jasa laundry profesional di Kendari, Sulawesi Tenggara dengan layanan antar jemput gratis untuk mahasiswa dan pekerja sibuk. Cuci + lipat mulai Rp 5.000/kg.',
            'url' => url('/'),
            'telephone' => '+6282156912202',
            'priceRange' => 'Rp 5.000 - Rp 7.000',
            'currenciesAccepted' => 'IDR',
            'paymentAccepted' => 'Cash, Transfer, E-Wallet',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Kendari',
                'addressRegion' => 'Sulawesi Tenggara',
                'addressCountry' => 'ID',
            ],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Kendari, Sulawesi Tenggara',
                'addressCountry' => 'ID',
            ],
            'logo' => asset('images/Logo.png'),
            'image' => [asset('images/Logo.png')],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            ],
            'openingHours' => 'Mo-Su 08:00-21:00',
            'serviceType' => 'Laundry Service',
            'slogan' => 'Tetap Aktif, Tetap Bersih',
            'foundingDate' => '2024',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Laundry Services',
                'itemListElement' => [
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'Cuci + Lipat',
                            'description' =>
                                'Layanan cuci dan lipat rapi dengan parfum premium dan gratis antar jemput',
                        ],
                        'price' => '5000',
                        'priceCurrency' => 'IDR',
                        'availability' => 'https://schema.org/InStock',
                        'validFrom' => '2024-01-01',
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'Cuci + Setrika',
                            'description' =>
                                'Layanan cuci bersih dan setrika rapi dengan parfum premium dan gratis antar jemput',
                        ],
                        'price' => '7000',
                        'priceCurrency' => 'IDR',
                        'availability' => 'https://schema.org/InStock',
                        'validFrom' => '2024-01-01',
                    ],
                ],
            ],
            'sameAs' => [
                'https://www.tiktok.com/@miegacoanlevel01',
                'https://www.instagram.com/aktif_laundry',
                'https://wa.me/6282156912202',
                'https://aktiflaundry.com',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '5',
                'reviewCount' => '1',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($structuredData) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
                <section id="beranda" class="h-dvh w-full hero scroll-mt-auto"
                    style="background-image: url('{{ asset('images/bg-hero.webp') }}');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content text-neutral-content text-center">
                        <div class="max-w-full space-y-5">
                            <h1 class="text-2xl md:text-4xl font-bold">"Jangan Biarkan Baju Kotor <br
                                    class="lg:hidden" />
                                Menghambat Aktifitas Lu"</h1>
                            <p
                                class="text-lg md:text-xl lg:text-2xl font-semibold bg-primary p-2 rounded-lg inline-block">
                                Tetap Aktif, Tetap Bersih !!!</p>
                            <p
                                class="text-primary-content max-w-2xl mx-auto font-light text-sm md:text-md leading-relaxed px-4">
                                Kamu punya gaya hidup aktif - kerja, kuliah, nongki & olahraga.
                                Biar kami yang jaga kebersihan & kenyamananmu.
                                Dengan layanan <span class="uppercase">cuci-setrika-lipat</span> hanya
                                <span class="font-semibold">Rp. 5.000/kg + Gratis Antar-Jemput SE-KENDARI</span>
                            </p>
                        </div>
                    </div>
                </section>

                <section id="smart-clean" class="min-h-dvh bg-base-100 w-full scroll-mt-auto py-20">
                    <div class="container mx-auto px-4">
                        <!-- Headline Section -->
                        <div class="text-center mb-16 max-w-4xl mx-auto">
                            <h2 class="text-2xl lg:text-4xl font-bold text-primary mb-6">
                                Aktif Mengekspresikan Diri, <br class="lg:hidden" />Bebas Jalani Lifestyle, <br
                                    class="lg:hidden" />Karena Hidup Bersih Itu Smart!
                            </h2>
                            <div class="w-24 h-1 bg-primary mx-auto mb-8 rounded-full"></div>

                            <!-- Narasi Marketing 3.0 -->
                            <div class="space-y-6 text-base-content/80 text-md leading-relaxed">
                                <p class="max-w-2xl mx-auto">
                                    <span class="font-semibold">{{ config('app.name') }}</span>,
                                    <br class="hidden sm:inline" />
                                    hadir buat kamu yang ngga mau ribet tapi tetap mau tampil maksimal.<br
                                        class="hidden sm:inline" /> Kami ngerti hidup kamu yang cepat, dinamis, dan
                                    penuh gaya.
                                </p>
                            </div>
                        </div>

                        <!-- Manfaat Utama -->
                        <div
                            class="flex md:grid md:grid-cols-3 gap-4 max-w-6xl mx-auto mt-16 overflow-x-auto no-scrollbar p-4">

                            <div class="flex-none w-[74dvw] card bg-base-200 shadow-md">
                                <figure>
                                    <img src="https://static.vecteezy.com/system/resources/previews/051/489/628/non_2x/young-corporate-employees-sitting-in-office-looking-at-laptop-screen-reading-good-news-excited-happy-team-diverse-people-celebrating-victory-win-command-success-excellent-result-professional-teamwork-free-photo.jpg"
                                        alt="Laundry hemat waktu - Mahasiswa bisa fokus belajar dan beraktivitas dengan layanan laundry Aktif Laundry"
                                        class="w-full h-48 object-cover" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Aktif Bekerja</h2>
                                    <p class="text-base-content/70">
                                        Selalu tampil rapi & wangi tanpa buang waktu dan tetap produktif
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Productivity</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-none w-[74dvw] card bg-base-200 shadow-md">
                                <figure>
                                    <img src="https://static.vecteezy.com/system/resources/previews/026/797/585/non_2x/young-couple-running-through-the-park-and-listening-to-music-healthy-living-concept-free-photo.jpg"
                                        alt="Laundry meningkatkan produktivitas - Layanan laundry antar jemput untuk pekerja sibuk dan mahasiswa"
                                        class="w-full h-48 object-cover" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Aktif Olahraga</h2>
                                    <p class="text-base-content/70">
                                        Tetap fit & sehat tampil percaya diri dengan outfit yang selalu bersih dan wangi
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Fit</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-none w-[74dvw] card bg-base-200 shadow-md">
                                <figure>
                                    <img src="https://static.vecteezy.com/system/resources/previews/012/712/418/non_2x/cute-smiling-women-drinking-a-coffee-free-photo.jpg"
                                        alt="Laundry hemat waktu - Mahasiswa bisa fokus belajar dan beraktivitas dengan layanan laundry Aktif Laundry"
                                        class="w-full h-48 object-cover" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Aktif Nongki</h2>
                                    <p class="text-base-content/70">
                                        Semakin gaya & pede saat nongki karena outfit-mu selalu rapi dan stylish
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Stylish</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-none w-[74dvw] card bg-base-200 shadow-md">
                                <figure>
                                    <img src="https://static.vecteezy.com/system/resources/previews/046/842/834/non_2x/young-man-student-with-notebooks-showing-thumb-free-photo.jpg"
                                        alt="Berkualitas dengan laundry profesional - Baju wangi dan rapi untuk menunjang percaya diri"
                                        class="w-full h-48 object-cover" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Aktif Ngampus</h2>
                                    <p class="text-base-content/70">
                                        Jalani hidup smart dengan laundry hemat, waktu & energimu semakin aktif
                                        berekspresi
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Smart</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="text-center mt-20">
                            <x-button no-wire-navigate link="#pesan" label="Kami Bukan Cuman Cuci Pakaian!"
                                class="btn-primary btn-lg" />
                            <p class="text-base-content/60 mt-4 text-sm">
                                Tetapi bantu kamu hidup lebih ringan lebih bersih, smart, & berenergi!
                            </p>
                        </div>
                    </div>
                </section>

                <section id="pesan" class="min-h-dvh bg-base-300 w-full scroll-mt-auto py-20">
                    <div class="container mx-auto px-4">
                        <!-- Headline Section -->
                        <div class="text-center mb-16 max-w-4xl mx-auto">
                            <h2 class="text-2xl lg:text-4xl font-bold text-success mb-6">
                                Mulai Hari Ini, <br> Jadilah Versi Aktif Terbersih Dirimu
                            </h2>
                            <div class="w-24 h-1 bg-success mx-auto mb-8 rounded-full"></div>

                            <!-- Narasi Penawaran -->
                            <div class="space-y-6 text-base-content/80 text-md leading-relaxed">
                                <p class="max-w-2xl mx-auto">
                                    Cucianmu kami urus, kamu bebas fokus jalani aktivitasmu
                                    dengan harga <span class="font-semibold text-primary">Rp 5.000/kg</span> layanan
                                    spesialis <span class="uppercase font-bold text-primary">cuci-setrika-lipat</span> +
                                    <span class="font-semibold text-primary">Free antar-jemput Se-Kendari</span>
                                </p>
                            </div>
                        </div>

                        <!-- Layanan Pricing -->
                        <div
                            class="flex md:grid md:grid-cols-3 gap-4 max-w-6xl mx-auto mt-16 overflow-x-auto no-scrollbar p-4 scroll-smooth">
                            <div class="flex-none w-[74dvw] card bg-base-100 shadow-sm border-2 border-success h-full">
                                <div class="card-body">
                                    <span
                                        class="badge badge-md md:badge-lg badge-success badge-soft border border-dashed border-success">Layanan
                                        Paling
                                        Laris</span>
                                    <div class="flex justify-between items-center mt-4">
                                        <h2 class="text-md md:text-lg font-bold text-success">Paket Lengkap</h2>
                                        <span class="text-sm md:text-md font-normal text-success">Rp 5.000/kg</span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-xs">
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-success" />
                                            <span>Cuci + Setrika + lipat </span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-success" />
                                            <span>Parfum premium gratis</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-success" />
                                            <span>Estimasi 1 hari</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-success" />
                                            <span>Gratis antar-jemput</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6">
                                        <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20pesan%20Paket%20Lengkap%20Rp%205.000/kg"
                                            target="_blank" class="btn btn-success btn-block text-success-content">
                                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                                            Ambil Paket
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-none w-[74dvw] card bg-base-100 shadow-sm border border-primary h-full">
                                <div class="card-body">
                                    <span
                                        class="badge badge-md md:badge-lg badge-primary badge-soft border border-primary">
                                        Layanan Cuci
                                        Satuan</span>
                                    <div class="flex justify-between items-center mt-4">
                                        <h2 class="text-md md:text-lg font-bold text-primary">Mulai Dari</h2>
                                        <span class="text-sm md:text-md font-normal text-primary">Rp 10.000/kg</span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-sm">
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-primary" />
                                            <span>Seprai Rp 10.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-primary" />
                                            <span>Selimut Rp 15.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-primary" />
                                            <span>BedCover Rp 25.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-2 me-1 inline-block text-primary" />
                                            <span>Gratis antar-jemput</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6">
                                        <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20pesan%20Item%20Satuan%20(Seprai/Selimut/BedCover)"
                                            target="_blank" class="btn btn-primary btn-block text-primary-content">
                                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                                            Ambil Paket
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="text-center mt-14">
                            <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20nyuci%20nih.%20Jemputin%20dong..."
                                target="_blank" class="btn btn-success btn-lg text-success-content">
                                <x-icon name="o-chat-bubble-left-right" class="w-5 h-5 mr-2" />
                                PESAN SEKARANG
                            </a>
                            <div class="mt-12">
                                <p class="text-primary font-bold text-2xl md:text-2xl uppercase">
                                    {{ config('app.name') }}
                                </p>
                                <p class="text-primary font-semibold text-md md:text-lg">
                                    #TetapAktif #TetapBersih
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
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
    @livewireScripts
</body>

</html>

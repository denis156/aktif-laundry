<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth no-scrollbar" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Tetap Aktif, Tetap Bersih</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
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
                            <h1 class="text-3xl md:text-5xl font-bold">Jangan Biarin Baju Kotor <br class="lg:hidden" />
                                Menghentikan Gaya Lo.</h1>
                            <h2
                                class="text-xl md:text-2xl lg:text-4xl font-semibold bg-primary px-4 py-2 rounded-lg inline-block">
                                Tetap Aktif, Tetap Bersih !!!</h2>
                            <p
                                class="text-primary-content max-w-md sm:max-w-lg md:max-w-2xl mx-auto font-light text-md md:text-xl leading-relaxed px-4">
                                Buat lo yang sibuk ngampus, <span class="hidden sm:inline"><br /></span>padat jadwal
                                olahraga, atau gak ada waktu buat nongki karena tumpukan baju kotor... kami ngerti.
                                <span class="block sm:inline mt-2 sm:mt-0">Waktumu terlalu berharga untuk dibuang cuma
                                    buat nungguin mesin cuci.</span>
                            </p>
                        </div>
                    </div>
                </section>

                <section id="smart-clean" class="min-h-dvh bg-base-100 w-full scroll-mt-auto py-20">
                    <div class="container mx-auto px-4">
                        <!-- Headline Section -->
                        <div class="text-center mb-16 max-w-4xl mx-auto">
                            <h2 class="text-2xl lg:text-4xl font-bold text-primary mb-6">
                                Lebih dari Cuci Baju, <br class="lg:hidden" />Ini Tentang Kebebasanmu.
                            </h2>
                            <div class="w-24 h-1 bg-primary mx-auto mb-8 rounded-full"></div>

                            <!-- Narasi Marketing 3.0 -->
                            <div class="space-y-6 text-base-content/80 text-md leading-relaxed">
                                <p class="max-w-2xl mx-auto">
                                    Di <span class="font-semibold text-primary">{{ config('app.name') }}</span>, kami<br
                                        class="hidden sm:inline" />
                                    percaya bahwa setiap orang punya hak untuk bebas mengekspresikan diri dan
                                    menjalani<br class="hidden sm:inline" />
                                    lifestyle tanpa batas. Masa depanmu, karirmu, prestasi olahragamu, dan tawamu
                                    bareng<br class="hidden sm:inline" />
                                    teman-teman jauh lebih berharga.
                                </p>
                            </div>
                        </div>

                        <!-- Manfaat Utama -->
                        <div
                            class="flex md:grid md:grid-cols-3 gap-4 max-w-6xl mx-auto mt-16 overflow-x-auto no-scrollbar p-4">
                            <!-- Manfaat 1: Bebas Jalani Lifestyle -->
                            <div class="flex-none w-80 md:w-auto card bg-base-200 shadow-md">
                                <figure>
                                    <img src="{{ asset('images/more-time.jpg') }}" alt="Bebas Jalani Lifestyle"
                                        class="w-full h-48 object-cover"
                                        loading="lazy" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Bebas Jalani Lifestyle</h2>
                                    <p class="text-base-content/70">
                                        Lebih banyak waktu untuk latihan, kuliah, atau sekadar ngopi bareng teman.
                                        Hidupmu harusnya fokus pada growth, bukan pada tumpukan baju kotor.
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">More Time</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Manfaat 2: Hidup Lebih Smart -->
                            <div class="flex-none w-80 md:w-auto card bg-base-200 shadow-md">
                                <figure>
                                    <img src="{{ asset('images/productivity.jpg') }}" alt="Hidup Lebih Smart"
                                        class="w-full h-48 object-cover"
                                        loading="lazy" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Hidup Lebih Smart</h2>
                                    <p class="text-base-content/70">
                                        Manfaatkan waktu luangmu untuk hal produktif atau sekadar recharge, bukan untuk
                                        urusan domestik. Delegate laundry, focus on life.
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Productivity</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Manfaat 3: Bebas Ekspresi Diri -->
                            <div class="flex-none w-80 md:w-auto card bg-base-200 shadow-md">
                                <figure>
                                    <img src="{{ asset('images/confidence.jpg') }}" alt="Bebas Ekspresi Diri"
                                        class="w-full h-48 object-cover"
                                        loading="lazy" />
                                </figure>
                                <div class="card-body border-t-2 border-base-content border-dashed">
                                    <h2 class="card-title text-success">Bebas Ekspresi Diri</h2>
                                    <p class="text-base-content/70">
                                        Dari baju olahraga hingga outfit nongki, semua siap pakai, wangi, dan rapi. Kamu
                                        selalu tampil percaya diri dalam setiap occasion.
                                    </p>
                                    <div class="card-actions justify-end">
                                        <span class="badge badge-success badge-outline">Confidence</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="text-center mt-20">
                            <x-button no-wire-navigate link="#pesan" label="Yuk Mulai Hidup Lebih Bebas"
                                icon="o-arrow-down" class="btn-primary btn-lg" />
                            <p class="text-base-content/60 mt-4 text-sm">
                                Karena waktumu terlalu berharga untuk urusan laundry
                            </p>
                        </div>
                    </div>
                </section>

                <section id="pesan" class="min-h-dvh bg-base-300 w-full scroll-mt-auto py-20">
                    <div class="container mx-auto px-4">
                        <!-- Headline Section -->
                        <div class="text-center mb-16 max-w-4xl mx-auto">
                            <h2 class="text-2xl lg:text-4xl font-bold text-success mb-6">
                                Cuci Bersih Tanpa Beban <br class="lg:hidden" />Hanya 5 Ribu/Kg!
                            </h2>
                            <div class="w-24 h-1 bg-success mx-auto mb-8 rounded-full"></div>

                            <!-- Narasi Penawaran -->
                            <div class="space-y-6 text-base-content/80 text-md leading-relaxed">
                                <p class="max-w-2xl mx-auto">
                                    "Yuk Mulai Hidup Lebih Bebas" dimulai dari sini!<br class="hidden sm:inline" />
                                    Bebas dari baju kotor tanpa bikin kantong bolong.<br class="hidden sm:inline" />
                                    Cuci bersih mulai dari Rp 5.000/kg aja.<br class="hidden sm:inline" />
                                    Tinggal WhatsApp, kami yang jemput!
                                </p>
                            </div>
                        </div>

                        <!-- Layanan Pricing -->
                        <div class="flex md:grid md:grid-cols-3 gap-4 max-w-6xl mx-auto mt-16 overflow-x-auto no-scrollbar p-4 scroll-smooth"
                            x-data="{
                                scrollToCenter() {
                                    if (window.innerWidth < 768) {
                                        const card = $refs.cuciLipatCard;
                                        const container = $el;
                                        const cardRect = card.getBoundingClientRect();
                                        const containerRect = container.getBoundingClientRect();
                                        const scrollPosition = (cardRect.left - containerRect.left) + container.scrollLeft - (containerRect.width / 2) + (cardRect.width / 2);
                                        container.scrollTo({ left: scrollPosition, behavior: 'smooth' });
                                    }
                                }
                            }" x-init="$nextTick(() => { setTimeout(() => scrollToCenter(), 500); })" id="pricing-cards">
                            <!-- Cuci Setrika -->
                            <div
                                class="flex-none w-80 md:w-full card bg-base-100 shadow-sm border border-primary h-full">
                                <div class="card-body">
                                    <span
                                        class="badge badge-md md:badge-lg badge-primary badge-soft border border-primary">Paket
                                        Lengkap</span>
                                    <div class="flex justify-between items-center mt-4">
                                        <h2 class="text-xl md:text-2xl font-bold text-primary">Cuci + Setrika</h2>
                                        <span class="text-xl font-bold text-primary">Rp 7.000/kg</span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-sm">
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Cuci bersih + setrika rapi</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Parfum premium pilihan</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Estimasi 1 hari</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Gratis antar-jemput</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6">
                                        <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20pesan%20Cuci%20Setrika%20Rp%207.000/kg"
                                            target="_blank" class="btn btn-primary btn-block text-primary-content">
                                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                                            Pesan Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Cuci + Lipat -->
                            <div x-ref="cuciLipatCard"
                                class="flex-none w-80 md:w-full card bg-base-100 shadow-sm border-2 border-success h-full scale-102">
                                <div class="card-body">
                                    <span
                                        class="badge badge-md md:badge-lg badge-success badge-soft border border-dashed border-success">Paling
                                        Laris</span>
                                    <div class="flex justify-between items-center mt-4">
                                        <h2 class="text-xl md:text-2xl font-bold text-success">Cuci + Lipat</h2>
                                        <span class="text-xl font-bold text-success">Rp 5.000/kg</span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-sm">
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-success" />
                                            <span>Cuci bersih + lipat rapi</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-success" />
                                            <span>Parfum premium gratis</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-success" />
                                            <span>Estimasi 1 hari</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-success" />
                                            <span>Gratis antar-jemput</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6">
                                        <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20pesan%20Cuci%20Lipat%20Rp%205.000/kg"
                                            target="_blank" class="btn btn-success btn-block text-success-content">
                                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                                            Pesan Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Cuci Satuan -->
                            <div
                                class="flex-none w-80 md:w-full card bg-base-100 shadow-sm border border-primary h-full">
                                <div class="card-body">
                                    <span
                                        class="badge badge-md md:badge-lg badge-primary badge-soft border border-primary">Cuci
                                        Satuan</span>
                                    <div class="flex justify-between items-center mt-4">
                                        <h2 class="text-xl md:text-2xl font-bold text-primary">Mulai Dari</h2>
                                        <span class="text-xl font-bold text-primary">Rp 10.000/kg</span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-sm">
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Seprai Rp 10.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Gorden Rp 20.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>BedCover Rp 30.000</span>
                                        </li>
                                        <li>
                                            <x-icon name="o-check-circle"
                                                class="size-4 me-2 inline-block text-primary" />
                                            <span>Gratis antar-jemput</span>
                                        </li>
                                    </ul>
                                    <div class="mt-6">
                                        <a href="https://wa.me/6282156912202?text=Mimin%20aktif%20laundry,%20saya%20mau%20pesan%20Item%20Satuan%20(Seprai/Gorden/BedCover)"
                                            target="_blank" class="btn btn-primary btn-block text-primary-content">
                                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                                            Pesan Sekarang
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
                                JEMPUTIN BAJU KUY!
                            </a>
                            <p class="text-base-content/60 mt-4 text-sm">
                                Klik tombol di atas untuk hubungi WhatsApp kami sekarang juga & klaim layanan free
                                antar-jemput pertama kamu!
                            </p>
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

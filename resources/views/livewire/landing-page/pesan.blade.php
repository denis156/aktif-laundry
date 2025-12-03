<section id="pesan" class="min-h-dvh bg-base-300 w-full scroll-mt-auto py-20">
    <div class="container mx-auto px-4">
        <!-- Headline Section -->
        <div class="text-center mb-16 max-w-4xl mx-auto">
            <h2 class="text-2xl lg:text-4xl font-bold text-success mb-6">
                Mulai Hari Ini, <br> Jadilah Versi AKTIF Terbersih Dirimu
            </h2>
            <div class="w-24 h-1 bg-success mx-auto mb-8 rounded-full"></div>

            <!-- Narasi Penawaran -->
            <div class="space-y-6 text-base-content/80 text-md leading-relaxed">
                <p class="max-w-2xl mx-auto">
                    Cucianmu kami urus, kamu bebas fokus jalani aktivitasmu
                    dengan harga <span class="font-semibold text-primary">Rp 5.000/kg</span> layanan
                    spesialis <span class="uppercase font-bold text-primary">cuci-setrika-lipat</span> +
                    <span class="font-semibold text-primary">Free antar-jemput Seluruh Kota Kendari</span>
                </p>
            </div>
        </div>

        <!-- Layanan Pricing -->
        <div
            class="flex md:grid md:grid-cols-2 gap-4 md:gap-6 max-w-4xl mx-auto mt-16 overflow-x-auto md:overflow-visible no-scrollbar p-4 scroll-smooth">
            <div
                class="flex-none w-[74dvw] md:w-auto card bg-base-100 shadow-sm hover:shadow-lg border-2 border-success h-full transition-all duration-300">
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
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-success" />
                            <span>Cuci + Setrika + lipat </span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-success" />
                            <span>Parfum premium gratis</span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-success" />
                            <span>Estimasi 1 hari</span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-success" />
                            <span>Gratis antar-jemput</span>
                        </li>
                    </ul>
                    <div class="mt-6">
                        <a href="{{ $this->getWhatsappPaketLengkap() }}"
                            target="_blank" class="btn btn-success btn-block text-success-content">
                            <x-icon name="o-arrow-right" class="w-5 h-5 mr-2" />
                            Ambil Paket
                        </a>
                    </div>
                </div>
            </div>

            <div
                class="flex-none w-[74dvw] md:w-auto card bg-base-100 shadow-sm hover:shadow-lg border border-primary h-full transition-all duration-300">
                <div class="card-body">
                    <span class="badge badge-md md:badge-lg badge-primary badge-soft border border-primary">
                        Layanan Cuci
                        Satuan</span>
                    <div class="flex justify-between items-center mt-4">
                        <h2 class="text-md md:text-lg font-bold text-primary">Mulai Dari</h2>
                        <span class="text-sm md:text-md font-normal text-primary">Rp 10.000/kg</span>
                    </div>
                    <ul class="mt-6 flex flex-col gap-2 text-sm">
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-primary" />
                            <span>Seprai Rp 10.000</span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-primary" />
                            <span>Selimut Rp 15.000</span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-primary" />
                            <span>BedCover Rp 25.000</span>
                        </li>
                        <li>
                            <x-icon name="o-check-circle" class="size-2 me-1 inline-block text-primary" />
                            <span>Dan Lain-lain</span>
                        </li>
                    </ul>
                    <div class="mt-6">
                        <a href="{{ $this->getWhatsappItemSatuan() }}"
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
            <a href="{{ $this->getWhatsappCta() }}"
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

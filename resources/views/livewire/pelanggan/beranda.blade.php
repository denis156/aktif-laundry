<div class="container mx-auto">
    <div class="w-full space-y-8 flex flex-col justify-center items-center mb-24">
        <x-card class="w-full shadow-lg border border-primary">
            <x-slot:figure class="flex justify-between h-28 border-b border-dashed">
                <div class="p-4 space-y-auto">
                    <h1 class="text-sm text-base-content/60 font-medium">Total Transaksi</h1>
                    <span class="text-5xl text-success font-bold">0</span>
                </div>
                <div class="p-4">
                    <x-button class="btn-primary btn-soft border border-primary rounded-lg h-18 w-14"
                        icon="iconpark.plus-o" />
                </div>
            </x-slot:figure>
            <div class="stats stats-vertical shadow-lg w-full">
                <div class="stat bg-success">
                    <div class="stat-figure">
                        <x-icon name="iconpark.checkone-o" class="h-6 text-success-content" />
                    </div>
                    <div class="stat-title text-xs font-bold text-success-content">Selesai</div>
                    <div class="stat-value text-sm font-bold text-success-content">0</div>
                    <div class="stat-desc text-success-content">Pesananmu yang selesai</div>
                </div>
                <div class="stat bg-error text-error-content">
                    <div class="stat-figure">
                        <x-icon name="iconpark.close-o" class="h-6 text-error-content" />
                    </div>
                    <div class="stat-title text-xs font-bold text-error-content">Batal</div>
                    <div class="stat-value text-sm font-bold text-error-content">0</div>
                    <div class="stat-desc text-error-content">Pesananmu yang dibatalkan</div>
                </div>
            </div>
        </x-card>

        {{-- list layanan --}}
        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/80 uppercase">Layanan Kami</h1>
            </div>
            <div class="w-full space-x-4 flex overflow-x-auto snap-x snap-mandatory no-scrollbar">
                <x-card title="Cuci Kering" subtitle="Rp 5.000/kg"
                    class="w-[78dvw] shadow-lg border border-b-5 border-r-5 border-warning text-warning flex-none snap-start">
                    <x-slot:menu>
                        <x-badge class="badge-sm badge-warning" value="Populer" />
                        <x-icon name="iconpark.washingmachineone-o" class="h-10 text-warning" />
                    </x-slot:menu>
                    <ul class="flex flex-col gap-2 text-xs">
                        <li class="text-warning">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-warning" />
                            <span>Estimasi 1x24 jam</span>
                        </li>
                        <li class="text-warning">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-warning" />
                            <span>Cuci bersih maksimal</span>
                        </li>
                        <li class="text-warning">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-warning" />
                            <span>Pewangi premium</span>
                        </li>
                        <li class="text-warning">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-warning" />
                            <span>Pakaian wangi tahan lama</span>
                        </li>
                        <li class="text-base-content/60">
                            <x-icon name="iconpark.more-o" class="w-4 h-4 me-2 inline-block text-base-content/60" />
                            <span>+2 keunggulan lainnya</span>
                        </li>
                    </ul>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Pesan" class="btn-sm btn-warning" />
                        </div>
                    </x-slot:actions>
                </x-card>

                <x-card title="Cuci Setrika" subtitle="Rp 7.000/kg"
                    class="w-[78dvw] shadow-lg border border-b-5 border-r-5 border-success text-success flex-none snap-start">
                    <x-slot:menu>
                        <x-icon name="iconpark.iron-o" class="h-10 text-success" />
                    </x-slot:menu>
                    <ul class="flex flex-col gap-2 text-xs">
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Estimasi 2x24 jam</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Cuci + setrika rapi</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Lipat rapi & wangi</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Siap pakai langsung</span>
                        </li>
                        <li class="text-base-content/60">
                            <x-icon name="iconpark.more-o" class="w-4 h-4 me-2 inline-block text-base-content/60" />
                            <span>+2 keunggulan lainnya</span>
                        </li>
                    </ul>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Pesan" class="btn-sm btn-success" />
                        </div>
                    </x-slot:actions>
                </x-card>

                <x-card title="Setrika Saja" subtitle="Rp 3.000/kg"
                    class="w-[78dvw] shadow-lg border border-b-5 border-r-5 border-success text-success flex-none snap-start">
                    <x-slot:menu>
                        <x-icon name="iconpark.clothesgloves-o" class="h-10 text-success" />
                    </x-slot:menu>
                    <ul class="flex flex-col gap-2 text-xs">
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Estimasi 1x24 jam</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Setrika rapi & presisi</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Lipat sesuai standar</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Hemat & praktis</span>
                        </li>
                        <li class="text-base-content/60">
                            <x-icon name="iconpark.more-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>+2 keunggulan lainnya</span>
                        </li>
                    </ul>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Pesan" class="btn-sm btn-success" />
                        </div>
                    </x-slot:actions>
                </x-card>

                <x-card title="Express" subtitle="Rp 10.000/kg"
                    class="w-[78dvw] shadow-lg border border-b-5 border-r-5 border-success text-success flex-none snap-start">
                    <x-slot:menu>
                        <x-icon name="iconpark.speed-o" class="h-10 text-success" />
                    </x-slot:menu>
                    <ul class="flex flex-col gap-2 text-xs">
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Estimasi 6 jam</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Prioritas tertinggi</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Cuci + setrika kilat</span>
                        </li>
                        <li class="text-success">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 me-2 inline-block text-success" />
                            <span>Untuk kebutuhan mendesak</span>
                        </li>
                        <li class="text-base-content/60">
                            <x-icon name="iconpark.more-o" class="w-4 h-4 me-2 inline-block text-base-content/60" />
                            <span>+2 keunggulan lainnya</span>
                        </li>
                    </ul>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Pesan" class="btn-sm btn-success" />
                        </div>
                    </x-slot:actions>
                </x-card>
            </div>
        </div>

        {{-- list promo --}}
        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/80 uppercase">Promo Spesial</h1>
            </div>
            <div class="w-full space-x-4 flex overflow-x-auto snap-x snap-mandatory no-scrollbar">
                <x-card title="Diskon 20%" subtitle="PROMO-001"
                    class="shadow-lg border border-b-5 border-r-5 border-info w-[78dvw] flex-none snap-start">
                    <div class="space-y-2">
                        <p class="text-sm text-base-content/80">Minimal 5kg, berlaku untuk member baru</p>
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.time-o" class="w-4 h-4 text-warning" />
                            <span class="text-xs text-base-content/60">Berlaku s/d 31 Des 2025</span>
                        </div>
                    </div>

                    <x-slot:figure>
                        <img src="https://picsum.photos/500/200"
                            class="border-b-2 border-dashed border-info h-[24dvh] w-full" />
                    </x-slot:figure>
                    <x-slot:menu>
                        <x-button icon="iconpark.shareone-o" class="btn-circle btn-sm btn-info btn-soft" />
                    </x-slot:menu>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Gunakan" class="btn-sm btn-info" />
                        </div>
                    </x-slot:actions>
                </x-card>

                <x-card title="Gratis Antar Jemput" subtitle="PROMO-002"
                    class="shadow-lg border border-b-5 border-r-5 border-info w-[78dvw] flex-none snap-start">
                    <div class="space-y-2">
                        <p class="text-sm text-base-content/80">Untuk transaksi minimal Rp 50.000</p>
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.local-o" class="w-4 h-4 text-success" />
                            <span class="text-xs text-base-content/60">Radius 5km dari toko</span>
                        </div>
                    </div>

                    <x-slot:figure>
                        <img src="https://picsum.photos/500/200"
                            class="border-b-2 border-dashed border-info h-[24dvh] w-full" />
                    </x-slot:figure>
                    <x-slot:menu>
                        <x-button icon="iconpark.shareone-o" class="btn-circle btn-sm btn-info btn-soft" />
                    </x-slot:menu>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Gunakan" class="btn-sm btn-info" />
                        </div>
                    </x-slot:actions>
                </x-card>

                <x-card title="Paket Hemat Kiloan" subtitle="PROMO-003"
                    class="shadow-lg border border-b-5 border-r-5 border-info w-[78dvw] flex-none snap-start">
                    <div class="space-y-2">
                        <p class="text-sm text-base-content/80">Cuci + Setrika hanya Rp 7.000/kg</p>
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success" />
                            <span class="text-xs text-base-content/60">Selesai 2x24 jam</span>
                        </div>
                    </div>

                    <x-slot:figure>
                        <img src="https://picsum.photos/500/200"
                            class="border-b-2 border-dashed border-info h-[24dvh] w-full" />
                    </x-slot:figure>
                    <x-slot:menu>
                        <x-button icon="iconpark.shareone-o" class="btn-circle btn-sm btn-info btn-soft" />
                    </x-slot:menu>
                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral" />
                            <x-button label="Gunakan" class="btn-sm btn-info" />
                        </div>
                    </x-slot:actions>
                </x-card>
            </div>
        </div>

        <x-card title="Untung Bareng Teman!" subtitle="Bagikan kode & nikmati promo spesial"
            class="w-full shadow-lg border border-primary col-span-full text-primary">

            {{-- Benefit Info --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                {{-- Benefit untuk Kamu --}}
                <div>
                    <p class="text-xs font-semibold text-success mb-2">Keuntungan Kamu:</p>
                    <div class="space-y-1">
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Diskon 20% layanan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Gratis ongkir 2x</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-success shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Voucher Rp 50.000</span>
                        </div>
                    </div>
                </div>

                {{-- Benefit untuk Teman --}}
                <div>
                    <p class="text-xs font-semibold text-info mb-2">Teman Kamu Dapat:</p>
                    <div class="space-y-1">
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-info shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Diskon 15% first order</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-info shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Gratis ongkir</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <x-icon name="iconpark.checkone-o" class="w-4 h-4 text-info shrink-0 mt-0.5" />
                            <span class="text-xs text-base-content/80">Voucher Rp 25.000</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Referral Code Input --}}
            <x-input label="Kode Referral Kamu" value="AKTIF2025" readonly>
                <x-slot:prepend>
                    <x-button class="join-item btn-success" label="Salin" />
                </x-slot:prepend>
                <x-slot:append>
                    <x-button icon="iconpark.refresh-o" class="join-item btn-primary" tooltip="Generate Baru" />
                </x-slot:append>
            </x-input>
        </x-card>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/80 uppercase">Pesanan Terbaru</h1>
                <x-button label="Lihat Semua" class="btn-ghost btn-xs btn-primary" />
            </div>
            <div class="text-center py-8">
                <x-icon name="iconpark.handlex-o" class="w-16 h-16 mx-auto text-base-content/40" />
                <p class="text-sm text-base-content/60 mt-2">Belum ada pesanan</p>
            </div>
        </div>
    </div>
</div>

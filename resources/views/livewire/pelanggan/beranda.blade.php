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
                <div class="stat bg-warning text-warning-content">
                    <div class="stat-figure">
                        <x-icon name="iconpark.close-o" class="h-6 text-warning-content" />
                    </div>
                    <div class="stat-title text-xs font-bold text-warning-content">Batal</div>
                    <div class="stat-value text-sm font-bold text-warning-content">0</div>
                    <div class="stat-desc text-warning-content">Pesananmu yang dibatalkan</div>
                </div>
            </div>
        </x-card>

        {{-- list layanan --}}
        <livewire:pelanggan.component.list-layanan>

        {{-- list promo --}}
        <livewire:pelanggan.component.list-promo>

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

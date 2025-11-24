<div>
    <x-header title="Pengaturan Konfigurasi" icon="o-adjustments-horizontal" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" subtitle="Konfigurasi Sistem & Toko" separator progress-indicator>
        <x-slot:subtitle>
            Kelola informasi dan pengaturan toko laundry
        </x-slot:subtitle>
    </x-header>

    <div class="max-w-6xl mx-auto">
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT SIDE: Form Cards (2 columns) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Card 1: Informasi Toko -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-primary/10 p-2 rounded-lg">
                                <x-icon name="o-building-storefront" class="w-6 h-6 text-primary" />
                            </div>
                            <h3 class="text-lg font-bold">Informasi Toko</h3>
                        </div>

                        <x-input
                            label="Nama Toko"
                            wire:model="nama_toko"
                            placeholder="Contoh: Aktif Laundry"
                            icon="o-building-storefront"
                            required
                            hint="Nama toko akan ditampilkan di struk" />
                    </x-card>

                    <!-- Card 2: Kontak -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-success/10 p-2 rounded-lg">
                                <x-icon name="o-phone" class="w-6 h-6 text-success" />
                            </div>
                            <h3 class="text-lg font-bold">Informasi Kontak</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input
                                label="WhatsApp"
                                wire:model="whatsapp"
                                prefix="+62"
                                placeholder="81234567890"
                                required
                                hint="Format: 8xxx (tanpa 0 atau +62)" />

                            <x-input
                                label="Email"
                                type="email"
                                wire:model="email"
                                placeholder="info@aktiflaundry.com"
                                icon="o-envelope"
                                required />
                        </div>
                    </x-card>

                    <!-- Card 3: Jam Operasional -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-warning/10 p-2 rounded-lg">
                                <x-icon name="o-clock" class="w-6 h-6 text-warning" />
                            </div>
                            <h3 class="text-lg font-bold">Jam Operasional</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-datetime
                                label="Jam Buka"
                                type="time"
                                wire:model="jam_buka"
                                icon="o-sun"
                                required />

                            <x-datetime
                                label="Jam Tutup"
                                type="time"
                                wire:model="jam_tutup"
                                icon="o-moon"
                                required />
                        </div>
                    </x-card>

                    <!-- Card 4: Format ID -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-info/10 p-2 rounded-lg">
                                <x-icon name="o-hashtag" class="w-6 h-6 text-info" />
                            </div>
                            <h3 class="text-lg font-bold">Format ID Otomatis</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input
                                label="Format ID Jenis Pakaian"
                                wire:model="format_id_jenis_pakaian"
                                placeholder="JNS"
                                icon="o-tag"
                                required
                                hint="Contoh: JNS, JP-, JENIS-" />

                            <x-input
                                label="Format ID Layanan"
                                wire:model="format_id_layanan"
                                placeholder="LYN"
                                icon="o-sparkles"
                                required
                                hint="Contoh: LYN, LY-, LAYANAN-" />

                            <x-input
                                label="Format ID Pelanggan"
                                wire:model="format_id_pelanggan"
                                placeholder="PLG"
                                icon="o-user"
                                required
                                hint="Contoh: PLG, PL-, PELANGGAN-" />

                            <x-input
                                label="Format ID Transaksi"
                                wire:model="format_id_transaksi"
                                placeholder="TRX"
                                icon="o-clipboard-document-list"
                                required
                                hint="Contoh: TRX, TR-, TRANSAKSI-" />

                            <x-input
                                label="Format ID Kurir"
                                wire:model="format_id_kurir"
                                placeholder="KUR"
                                icon="o-truck"
                                required
                                hint="Contoh: KUR, KR-, KURIR-" />

                            <x-input
                                label="Format ID Pengiriman"
                                wire:model="format_id_pengiriman"
                                placeholder="PNG"
                                icon="o-paper-airplane"
                                required
                                hint="Contoh: PNG, PN-, KIRIM-" />

                            <x-input
                                label="Format ID Pembayaran"
                                wire:model="format_id_pembayaran"
                                placeholder="PBY"
                                icon="o-credit-card"
                                required
                                hint="Contoh: PBY, PY-, BAYAR-" />

                            <x-input
                                label="Format ID Promo"
                                wire:model="format_id_promo"
                                placeholder="PROMO"
                                icon="o-gift"
                                required
                                hint="Contoh: PROMO, DISC-, PRO-" />

                            <x-input
                                label="Format ID Referral"
                                wire:model="format_id_referral"
                                placeholder="REF"
                                icon="o-users"
                                required
                                hint="Contoh: REF, RF-, REFER-" />
                        </div>
                    </x-card>

                    <!-- Card 5: Harga & Biaya -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-success/10 p-2 rounded-lg">
                                <x-icon name="o-banknotes" class="w-6 h-6 text-success" />
                            </div>
                            <h3 class="text-lg font-bold">Pengaturan Harga</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input
                                label="Biaya Antar Per KM"
                                type="number"
                                step="0.01"
                                wire:model="biaya_antar_per_km"
                                prefix="Rp"
                                placeholder="2000"
                                icon="o-truck"
                                required
                                hint="Biaya pengantaran per kilometer" />

                            <x-input
                                label="Minimum Berat (Kg)"
                                type="number"
                                step="0.1"
                                wire:model="min_berat_kg"
                                placeholder="2"
                                suffix="Kg"
                                icon="o-scale"
                                required
                                hint="Berat minimum untuk layanan kiloan" />

                            <x-input
                                label="Pajak"
                                type="number"
                                step="0.01"
                                wire:model="pajak_persen"
                                placeholder="10"
                                suffix="%"
                                icon="o-receipt-percent"
                                required
                                hint="Persentase pajak yang dikenakan" />
                        </div>
                    </x-card>

                    <!-- Card 6: Fitur Tambahan -->
                    <x-card class="shadow-md">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-base-300">
                            <div class="bg-secondary/10 p-2 rounded-lg">
                                <x-icon name="o-puzzle-piece" class="w-6 h-6 text-secondary" />
                            </div>
                            <h3 class="text-lg font-bold">Fitur Tambahan</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start justify-between p-4 bg-base-200/50 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-icon name="o-gift" class="w-5 h-5 text-primary" />
                                        <h4 class="font-semibold">Sistem Referral</h4>
                                    </div>
                                    <p class="text-sm text-base-content/70">
                                        Aktifkan sistem rujukan pelanggan untuk mendapatkan diskon atau bonus
                                    </p>
                                </div>
                                <x-toggle
                                    wire:model.live="enable_referral"
                                    class="ml-4"
                                />
                            </div>

                            <div class="flex items-start justify-between p-4 bg-base-200/50 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-icon name="o-ticket" class="w-5 h-5 text-primary" />
                                        <h4 class="font-semibold">Sistem Promo</h4>
                                    </div>
                                    <p class="text-sm text-base-content/70">
                                        Aktifkan sistem promosi dan diskon untuk pelanggan
                                    </p>
                                </div>
                                <x-toggle
                                    wire:model.live="enable_promo"
                                    class="ml-4"
                                />
                            </div>
                        </div>
                    </x-card>

                </div>

                <!-- RIGHT SIDE: Info & Actions (1 column) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-4 space-y-6">

                        <!-- Preview Card -->
                        <x-card class="shadow-lg bg-primary text-primary-content">
                            <div class="text-center space-y-3">
                                <div class="w-16 h-16 mx-auto bg-white/20 rounded-full flex items-center justify-center">
                                    <x-icon name="o-eye" class="w-8 h-8" />
                                </div>
                                <h3 class="text-xl font-bold">Preview Pengaturan</h3>
                                <div class="divider divider-neutral opacity-20"></div>

                                <div class="text-left space-y-2 bg-white/10 rounded-lg p-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Nama Toko:</span>
                                        <span class="font-bold">{{ $nama_toko ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">WhatsApp:</span>
                                        <span class="font-bold">{{ $whatsapp ? '+62' . $whatsapp : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Email:</span>
                                        <span class="font-bold text-xs break-all">{{ $email ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Jam Buka:</span>
                                        <span class="font-bold">{{ $jam_buka ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Jam Tutup:</span>
                                        <span class="font-bold">{{ $jam_tutup ?: '-' }}</span>
                                    </div>
                                    <div class="divider divider-neutral opacity-20 my-2"></div>
                                    <div class="text-xs opacity-80 mb-1">Format ID:</div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Jenis Pakaian:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_jenis_pakaian ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Layanan:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_layanan ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Pelanggan:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_pelanggan ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Transaksi:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_transaksi ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Kurir:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_kurir ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Pengiriman:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_pengiriman ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Pembayaran:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_pembayaran ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Promo:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_promo ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Referral:</span>
                                        <span class="font-bold font-mono text-xs">{{ $format_id_referral ?: '-' }}</span>
                                    </div>
                                    <div class="divider divider-neutral opacity-20 my-2"></div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Biaya Antar/KM:</span>
                                        <span class="font-bold">Rp {{ number_format($biaya_antar_per_km, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Min. Berat:</span>
                                        <span class="font-bold">{{ $min_berat_kg }} Kg</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Pajak:</span>
                                        <span class="font-bold">{{ $pajak_persen }}%</span>
                                    </div>
                                    <div class="divider divider-neutral opacity-20 my-2"></div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Referral:</span>
                                        <span class="font-bold">{{ $enable_referral ? 'Aktif' : 'Nonaktif' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Promo:</span>
                                        <span class="font-bold">{{ $enable_promo ? 'Aktif' : 'Nonaktif' }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-card>

                        <!-- Action Button -->
                        <x-button
                            label="Simpan Pengaturan"
                            type="submit"
                            spinner="save"
                            class="btn-primary btn-lg w-full shadow-lg"
                            icon="o-check" />
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>

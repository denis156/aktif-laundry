<div>
    <x-header title="Pengaturan" icon="o-cog-6-tooth" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" subtitle="Konfigurasi Sistem & Toko" separator progress-indicator>
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
                            wire:model="settings.nama_toko"
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
                                wire:model="settings.whatsapp"
                                placeholder="81234567890"
                                icon="o-chat-bubble-left-right"
                                required
                                hint="Format: 8xxx (tanpa 0 atau +62)" />

                            <x-input
                                label="Email"
                                type="email"
                                wire:model="settings.email"
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
                                wire:model="settings.jam_buka"
                                icon="o-sun"
                                required />

                            <x-datetime
                                label="Jam Tutup"
                                type="time"
                                wire:model="settings.jam_tutup"
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
                                wire:model="settings.format_id_jenis_pakaian"
                                placeholder="JP-"
                                icon="o-tag"
                                required
                                hint="Contoh: JP-, JENIS-, JP001" />

                            <x-input
                                label="Format ID Layanan"
                                wire:model="settings.format_id_layanan"
                                placeholder="LY-"
                                icon="o-sparkles"
                                required
                                hint="Contoh: LY-, LAYANAN-, LY001" />

                            <x-input
                                label="Format ID Pelanggan"
                                wire:model="settings.format_id_pelanggan"
                                placeholder="PL-"
                                icon="o-user"
                                required
                                hint="Contoh: PL-, PELANGGAN-, PL001" />

                            <x-input
                                label="Format ID Transaksi"
                                wire:model="settings.format_id_transaksi"
                                placeholder="TR-"
                                icon="o-clipboard-document-list"
                                required
                                hint="Contoh: TR-, TRANSAKSI-, TR001" />
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
                                        <span class="font-bold">{{ $settings['nama_toko'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">WhatsApp:</span>
                                        <span class="font-bold">{{ $settings['whatsapp'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Email:</span>
                                        <span class="font-bold text-xs break-all">{{ $settings['email'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Jam Buka:</span>
                                        <span class="font-bold">{{ $settings['jam_buka'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Jam Tutup:</span>
                                        <span class="font-bold">{{ $settings['jam_tutup'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Format ID Jenis Pakaian:</span>
                                        <span class="font-bold font-mono text-xs">{{ $settings['format_id_jenis_pakaian'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Format ID Layanan:</span>
                                        <span class="font-bold font-mono text-xs">{{ $settings['format_id_layanan'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Format ID Pelanggan:</span>
                                        <span class="font-bold font-mono text-xs">{{ $settings['format_id_pelanggan'] ?: '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="opacity-80">Format ID Transaksi:</span>
                                        <span class="font-bold font-mono text-xs">{{ $settings['format_id_transaksi'] ?: '-' }}</span>
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

<div>
    <x-header title="Edit Transaksi" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi transaksi
        </x-slot:subtitle>
        <x-slot:actions>
            <livewire:management.component.whats-app-button :transaksi-id="$transaksiId" size="md" :key="'wa-edit-'.$transaksiId" />
            <x-button label="Print Struk" wire:click="printReceipt" icon="o-printer" class="btn-secondary" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Transaksi section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Transaksi" subtitle="Data dasar transaksi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Transaksi"
                        wire:model="formData.kode_transaksi"
                        readonly
                        hint="Kode tidak dapat diubah"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Kasir"
                        value="{{ Auth::user()->name ?? 'Tidak diketahui' }}"
                        readonly
                        hint="Tidak dapat diubah"
                        icon="o-user-circle"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime
                        label="Tanggal Masuk"
                        type="datetime-local"
                        wire:model="formData.tanggal_masuk"
                        icon="o-calendar"
                        required
                    />

                    <x-select
                        label="Status"
                        wire:model="formData.status"
                        icon="o-flag"
                        :options="[
                            ['id' => 'Menunggu', 'name' => 'Menunggu'],
                            ['id' => 'Proses', 'name' => 'Proses'],
                            ['id' => 'Selesai', 'name' => 'Selesai'],
                            ['id' => 'Diambil', 'name' => 'Diambil'],
                            ['id' => 'Batal', 'name' => 'Batal']
                        ]"
                        option-value="id"
                        option-label="name"
                        required
                    />
                </div>

                <x-choices
                    label="Pelanggan"
                    wire:model.live="formData.pelanggan_id"
                    :options="$pelangganOptions"
                    option-label="nama"
                    option-sub-label="no_hp"
                    single
                    searchable
                    clearable
                    icon="o-user"
                    height="max-h-86"
                    hint="Ketik nama atau nomor HP untuk mencari"
                />
            </x-card>
        </div>

        {{-- Layanan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Layanan" subtitle="Edit layanan dalam transaksi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <livewire:management.component.multi-layanan-form :items="$multiLayananData['items']" />
            </x-card>
        </div>

        {{-- Pembayaran section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Pembayaran" subtitle="Detail pembayaran transaksi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input
                        label="Subtotal"
                        type="number"
                        wire:model.live="formData.subtotal"
                        readonly
                        prefix="Rp"
                        hint="Otomatis dihitung"
                    />

                    <x-input
                        label="Diskon"
                        type="number"
                        wire:model.live="formData.diskon"
                        placeholder="0"
                        prefix="Rp"
                        hint="Opsional"
                    />

                    <x-input
                        label="Total Bayar"
                        type="number"
                        wire:model.live="formData.total"
                        readonly
                        prefix="Rp"
                        hint="Subtotal - Diskon"
                        class="font-bold text-lg"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select
                        label="Metode Pembayaran"
                        wire:model="formData.metode_pembayaran"
                        icon="o-credit-card"
                        :options="[
                            ['id' => 'Tunai', 'name' => 'Tunai'],
                            ['id' => 'Transfer', 'name' => 'Transfer'],
                            ['id' => 'QRIS', 'name' => 'QRIS'],
                            ['id' => 'Debit', 'name' => 'Debit']
                        ]"
                        option-value="id"
                        option-label="name"
                        required
                    />

                    <x-datetime
                        label="Estimasi Selesai"
                        type="datetime-local"
                        wire:model="formData.tanggal_selesai"
                        icon="o-clock"
                        hint="Otomatis dari layanan"
                        readonly
                    />
                </div>

                <x-textarea
                    label="Catatan"
                    wire:model="formData.catatan"
                    placeholder="Catatan tambahan..."
                    rows="3"
                    hint="Opsional"
                />
            </x-card>
        </div>

        {{-- Promo & Referral section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Promo & Referral" subtitle="Opsional - Kode promo atau referral" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select
                        label="Kode Promo"
                        wire:model.live="selectedPromoId"
                        icon="o-ticket"
                        :options="$promoOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Pilih promo..."
                        hint="Diskon otomatis diterapkan"
                    />

                    <x-select
                        label="Kode Referral"
                        wire:model="selectedReferralId"
                        icon="o-user-group"
                        :options="$referralOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Pilih referral..."
                        hint="Untuk tracking referral"
                    />
                </div>
            </x-card>
        </div>

        {{-- Kurir section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Kurir" subtitle="Opsional - Kurir jemput dan antar" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select
                        label="Kurir Jemput"
                        wire:model="kurirJemputId"
                        icon="o-arrow-up-tray"
                        :options="$kurirOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Pilih kurir jemput..."
                        hint="Kurir yang menjemput cucian"
                    />

                    <x-select
                        label="Kurir Antar"
                        wire:model="kurirAntarId"
                        icon="o-arrow-down-tray"
                        :options="$kurirOptions"
                        option-value="id"
                        option-label="name"
                        placeholder="Pilih kurir antar..."
                        hint="Kurir yang mengantar cucian"
                    />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('transaksi.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>

    @script
        <script>
            $wire.on('open-print-window', (event) => {
                window.open(event.url, '_blank');
            });
        </script>
    @endscript
</div>

<div>
    <x-header title="Tambah Transaksi Baru" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('transaksi.index') }}" wire:navigate.hover icon="o-arrow-left" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card class="max-w-5xl mx-auto shadow-sm">
        <x-form wire:submit="save">
            <div class="space-y-5">
                {{-- Informasi Transaksi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Kode Transaksi"
                        wire:model="formData.kode_transaksi"
                        readonly
                        hint="Auto generate"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Kasir"
                        value="Admin (Kasir)"
                        readonly
                        hint="Auto dari login"
                        icon="o-user-circle"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
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

                {{-- Pelanggan & Layanan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select
                        label="Pelanggan"
                        wire:model="formData.pelanggan_id"
                        icon="o-user"
                        :options="$pelangganOptions"
                        option-value="id"
                        option-label="name"
                        required
                        placeholder="Pilih pelanggan"
                    />

                    <x-select
                        label="Layanan"
                        wire:model="formData.layanan_id"
                        icon="o-sparkles"
                        :options="$layananOptions"
                        option-value="id"
                        option-label="name"
                        required
                        placeholder="Pilih layanan"
                    />
                </div>

                {{-- Komponen Jenis Pakaian --}}
                <div class="border border-base-300 rounded-lg p-4">
                    <livewire:component.key-value-jenis-pakaian :value="$formData['jenis_pakaian']" />
                </div>

                {{-- Berat & Harga --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Berat (Kg)"
                        type="text"
                        wire:model.lazy="formData.berat_kg"
                        placeholder="Contoh: 8.5 atau 8,5"
                        suffix="kg"
                        hint="Bisa gunakan titik (8.5) atau koma (8,5)"
                        required
                    />

                    <x-input
                        label="Harga per Kg"
                        type="number"
                        wire:model="formData.harga_per_kg"
                        readonly
                        prefix="Rp"
                        hint="Otomatis dari layanan"
                    />
                </div>

                {{-- Subtotal, Diskon, Total dalam 1 baris 3 kolom --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input
                        label="Subtotal"
                        type="number"
                        wire:model="formData.subtotal"
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
                        wire:model="formData.total"
                        readonly
                        prefix="Rp"
                        hint="Subtotal - Diskon"
                        class="font-bold text-lg"
                    />
                </div>

                {{-- Metode Pembayaran & Tanggal Selesai --}}
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

                    <x-input
                        label="Tanggal Selesai"
                        type="datetime-local"
                        wire:model="formData.tanggal_selesai"
                        icon="o-clock"
                        hint="Estimasi selesai"
                    />
                </div>

                {{-- Catatan --}}
                <x-textarea
                    label="Catatan"
                    wire:model="formData.catatan"
                    placeholder="Catatan tambahan..."
                    rows="3"
                    hint="Opsional"
                />
            </div>

            <x-slot:actions>
                <x-button label="Batal" link="{{ route('transaksi.index') }}" wire:navigate.hover class="btn-ghost" />
                <x-button label="Simpan" type="submit" spinner="save" class="btn-primary" icon="o-check" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>

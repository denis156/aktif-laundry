<div>
    <x-header title="Tambah Transaksi Baru" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Kembali" link="{{ route('transaksi.index') }}" wire:navigate.hover icon="o-arrow-left" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card class="max-w-6xl mx-auto shadow-sm">
        <x-form wire:submit="save">
            <div class="space-y-5">
                {{-- Informasi Transaksi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-input
                        label="Kode Transaksi"
                        wire:model="formData.kode_transaksi"
                        readonly
                        hint="Auto generate"
                        icon="o-hashtag"
                    />

                    <x-input
                        label="Kasir"
                        value="{{ Auth::user()->name ?? 'Tidak diketahui' }}"
                        readonly
                        hint="Auto dari login"
                        icon="o-user-circle"
                    />

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

                {{-- Pelanggan --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Pelanggan</span>
                    </label>
                    <x-choices
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
                </div>

                {{-- Multi-Layanan Form --}}
                <div class="border border-base-300 rounded-lg p-4 bg-base-50">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <x-icon name="o-sparkles" class="w-5 h-5" />
                            Layanan
                        </h3>
                        <span class="text-sm text-base-content/70">Tambahkan satu atau lebih layanan</span>
                    </div>

                    <livewire:management.component.multi-layanan-form />
                </div>

                {{-- Subtotal, Diskon, Total --}}
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

                    <x-datetime
                        label="Estimasi Selesai"
                        type="datetime-local"
                        wire:model="formData.tanggal_selesai"
                        icon="o-clock"
                        hint="Otomatis dari layanan"
                        readonly
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

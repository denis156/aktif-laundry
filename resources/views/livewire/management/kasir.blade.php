<div>
    <!-- HEADER -->
    <x-header title="Kasir" icon="o-calculator" icon-classes="bg-primary text-primary-content  rounded-full p-1 w-8 h-8" subtitle="Point of Sale - Transaksi Cepat" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Daftar Transaksi" link="{{ route('transaksi.index') }}" wire:navigate.hover responsive icon="o-queue-list" class="btn-outline btn-primary" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT: FORM SECTION (2 kolom) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- CARD 1: DATA PELANGGAN -->
            <x-card class="shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <x-icon name="o-user" class="w-6 h-6" />
                    <h2 class="text-lg font-bold">Data Pelanggan</h2>
                </div>

                <!-- Mode Pelanggan Toggle -->
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-base-300">
                    <span class="text-sm font-medium text-gray-600">Mode Pelanggan</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm {{ !$isPelangganBaru ? 'font-bold text-primary' : 'text-gray-400' }}">
                            Pilih Pelanggan
                        </span>
                        <x-toggle wire:model.live="isPelangganBaru" />
                        <span class="text-sm {{ $isPelangganBaru ? 'font-bold text-success' : 'text-gray-400' }}">
                            Pelanggan Baru
                        </span>
                    </div>
                </div>

                <!-- Select Pelanggan - Always Visible -->
                <x-choices
                    label="Pilih Pelanggan"
                    wire:model.live="formData.pelanggan_id"
                    :options="$pelangganOptions"
                    option-label="nama"
                    option-sub-label="no_hp"
                    placeholder="Cari pelanggan ..."
                    single
                    searchable
                    clearable
                    icon="o-user"
                    height="max-h-86"
                    hint="Ketik nama atau nomor HP untuk mencari"
                    :disabled="$isPelangganBaru"
                />


                <!-- Form Detail Pelanggan - Always Visible -->
                <div class="space-y-4 mt-4">
                    <x-input
                        label="Nama Pelanggan"
                        wire:model="pelangganBaru.nama"
                        placeholder="Nama lengkap pelanggan"
                        required
                        :disabled="!$isPelangganBaru" />

                    <div class="grid grid-cols-2 gap-4">
                        <x-input
                            label="No. HP"
                            wire:model="pelangganBaru.no_hp"
                            placeholder="08xxx"
                            hint="Format: 08xxxxxxxxx"
                            required
                            :disabled="!$isPelangganBaru" />

                        <x-input
                            label="Email"
                            type="email"
                            wire:model="pelangganBaru.email"
                            placeholder="email@example.com"
                            :disabled="!$isPelangganBaru" />
                    </div>

                    <x-textarea
                        label="Detail Alamat"
                        wire:model="pelangganBaru.detail_alamat"
                        placeholder="Jalan, nomor rumah, RT/RW"
                        rows="2"
                        hint="Contoh: Jl. Merdeka No. 123, RT 01/RW 02"
                        required
                        :disabled="!$isPelangganBaru" />

                    <div class="grid grid-cols-2 gap-4">
                        <x-select
                            label="Kecamatan"
                            wire:model.live="pelangganBaru.kecamatan"
                            :options="$kecamatanOptions"
                            placeholder="Pilih kecamatan"
                            icon="o-map-pin"
                            :disabled="!$isPelangganBaru" />

                        <x-select
                            label="Kelurahan/Desa"
                            wire:model="pelangganBaru.kelurahan"
                            :options="$kelurahanOptions"
                            placeholder="Pilih kelurahan"
                            hint="{{ empty($pelangganBaru['kecamatan']) ? 'Pilih kecamatan dulu' : '' }}"
                            icon="o-map"
                            :disabled="!$isPelangganBaru || empty($pelangganBaru['kecamatan'])" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-input
                            label="Kabupaten/Kota"
                            wire:model="pelangganBaru.kabupaten_kota"
                            placeholder="Kota Kendari"
                            disabled />

                        <x-input
                            label="Provinsi"
                            wire:model="pelangganBaru.provinsi"
                            placeholder="Sulawesi Tenggara"
                            disabled />
                    </div>
                </div>
            </x-card>

            <!-- CARD 2: DETAIL TRANSAKSI -->
            <x-card class="shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <x-icon name="o-shopping-cart" class="w-6 h-6" />
                    <h2 class="text-lg font-bold">Detail Layanan</h2>
                </div>

                <div class="space-y-4">
                    <!-- Tanggal Masuk -->
                    <x-datetime
                        label="Tanggal Masuk"
                        type="datetime-local"
                        wire:model="formData.tanggal_masuk"
                        required />

                    <!-- Multi Layanan Form -->
                    <livewire:management.component.multi-layanan-form :key="'multi-layanan-'.$this->getId()" />

                    <!-- Diskon -->
                    <x-input
                        label="Diskon"
                        type="number"
                        wire:model.live="formData.diskon"
                        placeholder="0"
                        prefix="Rp"
                        hint="Opsional"
                        min="0"
                    />

                    <!-- Catatan -->
                    <x-textarea
                        label="Catatan"
                        wire:model="formData.catatan"
                        placeholder="Catatan tambahan (opsional)"
                        rows="3" />
                </div>
            </x-card>

            <!-- CARD 3: METODE PEMBAYARAN -->
            <x-card class="shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <x-icon name="o-credit-card" class="w-6 h-6" />
                    <h2 class="text-lg font-bold">Metode Pembayaran</h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-3 hover:bg-base-200 transition {{ $formData['metode_pembayaran'] == 'Tunai' ? 'bg-primary/10 border-primary' : '' }}">
                        <input
                            type="radio"
                            value="Tunai"
                            wire:model.live="formData.metode_pembayaran"
                            class="radio radio-primary" />
                        <span class="label-text font-medium">Tunai</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-3 hover:bg-base-200 transition {{ $formData['metode_pembayaran'] == 'Transfer' ? 'bg-primary/10 border-primary' : '' }}">
                        <input
                            type="radio"
                            value="Transfer"
                            wire:model.live="formData.metode_pembayaran"
                            class="radio radio-primary" />
                        <span class="label-text font-medium">Transfer</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-3 hover:bg-base-200 transition {{ $formData['metode_pembayaran'] == 'QRIS' ? 'bg-primary/10 border-primary' : '' }}">
                        <input
                            type="radio"
                            value="QRIS"
                            wire:model.live="formData.metode_pembayaran"
                            class="radio radio-primary" />
                        <span class="label-text font-medium">QRIS</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer border border-base-300 rounded-lg p-3 hover:bg-base-200 transition {{ $formData['metode_pembayaran'] == 'Debit' ? 'bg-primary/10 border-primary' : '' }}">
                        <input
                            type="radio"
                            value="Debit"
                            wire:model.live="formData.metode_pembayaran"
                            class="radio radio-primary" />
                        <span class="label-text font-medium">Debit</span>
                    </label>
                </div>
            </x-card>

        </div>

        <!-- RIGHT: RINGKASAN TRANSAKSI (1 kolom) -->
        <div class="lg:col-span-1">
            <div class="card bg-primary text-primary-content shadow-xl sticky top-4">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Ringkasan Transaksi</h2>

                    <!-- Summary Items -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-primary-content/20">
                            <span class="text-sm opacity-90">Kode:</span>
                            <span class="font-bold font-mono">{{ $formData['kode_transaksi'] }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-primary-content/20">
                            <span class="text-sm opacity-90">Pelanggan:</span>
                            <span class="font-bold text-right">
                                @if($isPelangganBaru && !empty($pelangganBaru['nama']))
                                    {{ $pelangganBaru['nama'] }}
                                @elseif(!empty($formData['nama_pelanggan']))
                                    {{ $formData['nama_pelanggan'] }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>

                        <!-- Layanan Summary -->
                        <div class="border-b border-primary-content/20 pb-3 mb-3">
                            <div class="text-sm opacity-90 mb-2 font-medium">Rincian Layanan:</div>
                            @if(!empty($multiLayananData))
                                @foreach($multiLayananData['items'] as $item)
                                    @if(!empty($item['layanan_id']))
                                        <div class="flex justify-between items-center py-1 text-sm">
                                            <span>{{ $item['nama_layanan'] ?? 'Layanan' }}:</span>
                                            <span class="font-medium">
                                                @if($item['tipe_layanan'] === 'per_kg')
                                                    {{ number_format((float) ($item['berat_kg'] ?? 0), 1, '.', '') }} kg
                                                @else
                                                    {{ $item['jumlah_satuan'] ?? 0 }} pcs
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-sm opacity-70">Belum ada layanan dipilih</div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-primary-content/20">
                            <span class="text-sm opacity-90">Subtotal:</span>
                            <span class="font-bold">Rp {{ number_format((float) ($multiLayananData['totalSubtotal'] ?? 0), 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-primary-content/20">
                            <span class="text-sm opacity-90">Diskon:</span>
                            <span class="font-bold text-warning">Rp {{ number_format((float) ($formData['diskon'] ?? 0), 0, ',', '.') }}</span>
                        </div>

                        <!-- TOTAL -->
                        <div class="flex justify-between items-center py-4 mt-2 bg-primary-content/20 rounded-lg px-4">
                            <span class="text-xl font-bold">TOTAL:</span>
                            <span class="text-3xl font-bold">Rp {{ number_format((float) ($multiLayananData['totalGrandTotal'] ?? 0), 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 mt-2">
                            <span class="text-sm opacity-90">Tanggal Selesai:</span>
                            <span class="font-medium">{{ $formData['tanggal_selesai'] ? \Carbon\Carbon::parse($formData['tanggal_selesai'])->format('d/m/Y H:i') : '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm opacity-90">Metode Pembayaran:</span>
                            <span class="font-bold">{{ $formData['metode_pembayaran'] }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-actions flex-col gap-2 mt-6">
                        <button
                            wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn btn-success btn-lg w-full text-white">
                            <x-icon name="o-check" class="w-5 h-5" />
                            <span wire:loading.remove wire:target="save">Proses Transaksi</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>

                        <button
                            wire:click="batalTransaksi"
                            class="btn btn-outline btn-sm w-full">
                            <x-icon name="o-arrow-path" class="w-4 h-4" />
                            Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PRINT RECEIPT MODAL -->
    <x-modal wire:model="showReceipt" title="Transaksi Berhasil!" subtitle="Apakah Anda ingin mencetak struk?" class="modal-bottom sm:modal-middle" persistent>
        <div class="text-center py-6">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-success flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Transaksi Berhasil Disimpan!</h3>
            <p class="text-sm text-gray-600">Kode: <span class="font-mono font-bold">{{ $lastTransactionId }}</span></p>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                <x-button
                    label="Nanti Saja"
                    @click="$wire.showReceipt = false"
                    class="btn-neutral btn-block" />
                <x-button
                    label="Cetak Struk"
                    wire:click="printReceipt"
                    class="btn-success btn-block"
                    icon="o-printer" />
            </div>
        </x-slot:actions>
    </x-modal>

    @script
        <script>
            $wire.on('open-print-window', (event) => {
                window.open(event.url, '_blank');
            });
        </script>
    @endscript
</div>

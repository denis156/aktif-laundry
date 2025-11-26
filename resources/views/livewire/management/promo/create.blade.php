<div>
    <x-header title="Tambah Promo Baru" separator progress-indicator>
        <x-slot:subtitle>
            Buat kode promo baru untuk pelanggan
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Detail identitas promo" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-file label="Banner Promo (Opsional)" wire:model="bannerImage"
                    accept="image/png, image/jpeg, image/jpg" hint="Ukuran maksimal 2MB. Format: JPG, PNG">
                    <img src="{{ asset('images/Logo.png') }}" class="h-32 rounded-lg" />
                </x-file>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Promo" wire:model="formData.kode_promo" placeholder="Auto Generate" readonly
                        hint="Kode dibuat otomatis" icon="o-hashtag" />

                    <x-input label="Nama Promo" wire:model="formData.nama_promo" placeholder="Contoh: Promo Tahun Baru"
                        icon="o-tag" required />
                </div>

                <x-textarea label="Deskripsi" wire:model="formData.deskripsi"
                    placeholder="Deskripsi promo (opsional)..." rows="3" hint="Opsional, jelaskan detail promo ini" />
            </x-card>
        </div>

        {{-- Pengaturan Diskon section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Pengaturan Diskon" subtitle="Tipe dan nilai diskon" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-select label="Tipe Diskon" wire:model.live="formData.tipe_diskon" :options="$tipeDiskonOptions"
                    option-value="id" option-label="name" required />

                @php
                $currentTipe = collect($tipeDiskonOptions)->firstWhere('id', $formData['tipe_diskon']);
                $suffix = $currentTipe['suffix'] ?? '';
                $hint = $currentTipe['hint'] ?? '';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nilai Diskon" wire:model="formData.nilai_diskon" type="number" :suffix="$suffix"
                        min="1" :hint="$hint" placeholder="Contoh: 10" required />

                    @if ($formData['tipe_diskon'] === 'persen')
                    <x-input label="Diskon Maksimal" wire:model="formData.diskon_maksimal" type="number" prefix="Rp"
                        min="1" hint="Batas maksimal diskon dalam rupiah (opsional)" placeholder="Contoh: 50000" />
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input label="Minimum Transaksi" wire:model="formData.min_transaksi" type="number" prefix="Rp"
                        min="1" hint="Min pembelian" placeholder="100000" />

                    <x-input label="Minimum Berat" wire:model="minBerat" type="number" suffix="Kg" min="0" step="0.1"
                        hint="Min berat" placeholder="3" />

                    <x-input label="Maksimum Berat" wire:model="maxBerat" type="number" suffix="Kg" min="0" step="0.1"
                        hint="Max berat" placeholder="10" />
                </div>

                <x-textarea label="Syarat & Ketentuan" wire:model="termsConditions"
                    placeholder="Tulis syarat dan ketentuan promo di sini..." rows="3"
                    hint="Opsional, akan ditampilkan ke pelanggan" />
            </x-card>
        </div>

        {{-- Periode & Kuota section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Periode & Kuota" subtitle="Waktu aktif dan batasan penggunaan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Tanggal Mulai" wire:model="formData.tanggal_mulai" type="date" icon="o-calendar"
                        required />

                    <x-input label="Tanggal Berakhir" wire:model="formData.tanggal_berakhir" type="date"
                        icon="o-calendar-days" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kuota Total" wire:model="formData.kuota_total" type="number" icon="o-ticket" min="1"
                        hint="Kosongkan untuk unlimited" placeholder="Contoh: 100" />

                    <x-input label="Max per User" wire:model="formData.max_per_user" type="number" icon="o-user" min="1"
                        hint="Berapa kali user bisa pakai (opsional)" placeholder="Contoh: 1" />
                </div>
            </x-card>
        </div>

        {{-- Target Promo section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Target Promo" subtitle="Siapa yang bisa pakai promo ini" size="text-lg" />
            </div>
            <x-card class="col-span-3" body-class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Status" wire:model="formData.status" icon="o-signal" :options="[
                            ['id' => 'Aktif', 'name' => 'Aktif'],
                            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
                        ]" option-value="id" option-label="name" required />

                    <x-select label="Berlaku Untuk" wire:model="formData.berlaku_untuk" icon="o-users" :options="[
                            ['id' => 'semua', 'name' => 'Semua Pelanggan'],
                            ['id' => 'pelanggan_baru', 'name' => 'Pelanggan Baru'],
                            ['id' => 'pelanggan_lama', 'name' => 'Pelanggan Lama'],
                        ]" option-value="id" option-label="name" required />
                </div>

                <x-choices-offline label="Layanan Yang Berlaku (Opsional)" wire:model="layananIds"
                    :options="$layananOptions" icon="o-sparkles" searchable placeholder="Cari layanan..."
                    hint="Kosongkan untuk semua layanan, atau pilih layanan tertentu" />

                <x-choices-offline label="Kecualikan Pelanggan (Opsional)" wire:model="excludePelangganIds"
                    :options="$pelangganOptions" icon="o-user-minus" searchable placeholder="Cari pelanggan..."
                    hint="Pilih pelanggan yang tidak boleh menggunakan promo ini" />

                <x-toggle label="Auto Apply" wire:model="autoApply"
                    hint="Promo otomatis digunakan saat checkout jika memenuhi syarat" right />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('promo.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

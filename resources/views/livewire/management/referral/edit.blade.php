<div>
    <x-header title="Edit Referral" separator progress-indicator>
        <x-slot:subtitle>
            Ubah data referral {{ $formData['kode_referral'] }}
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Detail identitas referral" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-choices label="Pilih Pelanggan" wire:model="formData.pelanggan_id" :options="$pelangganOptions"
                    option-value="id" option-label="nama" icon="o-user" searchable single
                    placeholder="Cari nama atau no hp..." hint="Pilih pelanggan yang akan memiliki kode referral"
                    search-function="pelangganSearch" min-chars="2" debounce="300ms" required />

                <x-input label="Kode Referral" wire:model="formData.kode_referral" placeholder="Contoh: REF001"
                    hint="Kode unik untuk referral ini" icon="o-hashtag" required />
            </x-card>
        </div>

        {{-- Promo Reward section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Promo Reward" subtitle="Promo untuk referrer dan referee" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-select label="Promo untuk Referrer (Yang Mengajak)" wire:model="formData.promo_referrer_id"
                    :options="$promoOptions" option-value="id" option-label="name" icon="o-gift"
                    placeholder="Pilih promo untuk yang mengajak..."
                    hint="Promo yang didapat pelanggan yang mengajak (pemilik kode)" />

                <x-select label="Promo untuk Referee (Yang Diajak)" wire:model="formData.promo_referee_id"
                    :options="$promoOptions" option-value="id" option-label="name" icon="o-ticket"
                    placeholder="Pilih promo untuk yang diajak..."
                    hint="Promo yang didapat pelanggan baru yang menggunakan kode" />

                <div class="divider">atau gunakan reward manual</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Poin Referrer" wire:model="formData.poin_referrer" type="number" suffix="Poin"
                        min="0" hint="Poin tambahan untuk pemilik kode" placeholder="10" />

                    <x-input label="Diskon Referee" wire:model="formData.diskon_referee" type="number" suffix="%"
                        min="0" max="100" hint="Diskon tambahan untuk pengguna kode" placeholder="10" />
                </div>

                <x-input label="Min. Transaksi Referee" wire:model="formData.min_transaksi_referee" type="number"
                    prefix="Rp" min="1" hint="Minimum transaksi untuk mendapat reward (opsional)"
                    placeholder="100000" />
            </x-card>
        </div>

        {{-- Statistik section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Statistik" subtitle="Data penggunaan referral" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat bg-base-200 rounded-lg p-4">
                        <div class="stat-title text-xs">Total Referral</div>
                        <div class="stat-value text-lg text-primary">{{ $formData['total_referral'] }}</div>
                        <div class="stat-desc">Pengguna kode</div>
                    </div>

                    <div class="stat bg-base-200 rounded-lg p-4">
                        <div class="stat-title text-xs">Berhasil</div>
                        <div class="stat-value text-lg text-success">{{ $formData['total_berhasil'] }}</div>
                        <div class="stat-desc">Transaksi sukses</div>
                    </div>

                    <div class="stat bg-base-200 rounded-lg p-4">
                        <div class="stat-title text-xs">Total Poin</div>
                        <div class="stat-value text-lg text-warning">{{ $formData['total_poin'] }}</div>
                        <div class="stat-desc">Poin terkumpul</div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Status section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status" subtitle="Status kode referral" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-select label="Status" wire:model="formData.status" icon="o-signal" :options="$statusOptions"
                    option-value="id" option-label="name" required />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('referral.index') }}" wire:navigate />
            <x-button label="Simpan Perubahan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

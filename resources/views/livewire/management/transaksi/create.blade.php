<div>
    <x-header title="Tambah Transaksi Baru" separator progress-indicator>
        <x-slot:subtitle>
            Buat transaksi laundry baru
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Transaksi section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Transaksi" subtitle="Data dasar transaksi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Transaksi" wire:model="formData.kode_transaksi" readonly hint="Auto generate"
                        icon="o-hashtag" />

                    <x-input label="Kasir" value="{{ Auth::user()->name ?? 'Tidak diketahui' }}" readonly
                        hint="Auto dari login" icon="o-user-circle" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime label="Tanggal Masuk" type="datetime-local" wire:model="formData.tanggal_masuk"
                        icon="o-calendar" required />

                    <x-select label="Status" wire:model="formData.status" icon="o-flag" :options="[
                            ['id' => 'Menunggu', 'name' => 'Menunggu'],
                            ['id' => 'Proses', 'name' => 'Proses'],
                            ['id' => 'Selesai', 'name' => 'Selesai'],
                            ['id' => 'Diambil', 'name' => 'Diambil'],
                            ['id' => 'Batal', 'name' => 'Batal']
                        ]" option-value="id" option-label="name" required />
                </div>

                <x-choices label="Pelanggan" wire:model.live="formData.pelanggan_id" :options="$pelangganOptions"
                    option-label="nama" option-sub-label="no_hp" single searchable clearable icon="o-user"
                    height="max-h-86" hint="Ketik nama atau nomor HP untuk mencari" />
            </x-card>
        </div>

        {{-- Layanan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Layanan" subtitle="Pilih layanan yang digunakan" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <livewire:management.component.multi-layanan-form />
            </x-card>
        </div>

        {{-- Pembayaran section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Pembayaran" subtitle="Detail pembayaran transaksi" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input label="Subtotal" type="number" wire:model.live="formData.subtotal" readonly prefix="Rp"
                        hint="Otomatis dihitung" />

                    <x-input label="Diskon" type="number" wire:model.live="promoResult.diskon" readonly prefix="Rp"
                        hint="Dari promo" />

                    <x-input label="Total Bayar" type="number" wire:model.live="formData.total" readonly prefix="Rp"
                        hint="Subtotal - Diskon" class="font-bold text-lg" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-select label="Metode Pembayaran (Kapan Bayar)" wire:model="formData.metode_pembayaran"
                        icon="o-credit-card" :options="$metodePembayaranOptions" option-value="id" option-label="name"
                        required hint="Bayar saat jemput atau antar" />

                    <x-select label="Tipe Bayar (Cara Bayar)" wire:model="formData.tipe_bayar" icon="o-banknotes"
                        :options="$tipeBayarOptions" option-value="id" option-label="name"
                        hint="Tunai atau Non-Tunai" />

                    <x-select label="Status Bayar" wire:model="formData.status_bayar" icon="o-check-circle"
                        :options="$statusBayarOptions" option-value="id" option-label="name" required
                        hint="Status pembayaran" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-datetime label="Estimasi Selesai" type="datetime-local" wire:model="formData.tanggal_selesai"
                        icon="o-clock" hint="Otomatis dari layanan" readonly />

                    <x-datetime label="Tanggal Bayar" type="datetime-local" wire:model="formData.tanggal_bayar"
                        icon="o-calendar" hint="Opsional - saat pembayaran" />

                    <x-input label="Jumlah Bayar" type="number" wire:model="formData.jumlah_bayar" prefix="Rp"
                        hint="Opsional - nominal yang dibayar" placeholder="0" />
                </div>

                <x-textarea label="Catatan" wire:model="formData.catatan" placeholder="Catatan tambahan..." rows="2"
                    hint="Opsional" />

                <x-textarea label="Catatan Internal" wire:model="formData.catatan_internal"
                    placeholder="Catatan untuk staff..." rows="2" hint="Opsional - Tidak terlihat di struk" />
            </x-card>
        </div>

        {{-- Promo & Referral section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Promo & Referral" subtitle="Opsional - Kode promo atau referral" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kode Promo" wire:model.live="formData.promo_id" icon="o-ticket"
                        :options="$promoOptions" option-value="id" option-label="name" placeholder="Pilih promo..."
                        hint="Diskon otomatis diterapkan" />

                    <x-select label="Kode Referral" wire:model="formData.referral_id" icon="o-user-group"
                        :options="$referralOptions" option-value="id" option-label="name"
                        placeholder="Pilih referral..." hint="Untuk tracking referral" />
                </div>

                @if($promoResult['valid'])
                <x-alert title="Promo Diterapkan!" description="{{ $promoResult['pesan'] }}" icon="o-check-circle"
                    class="alert-success mt-4" />
                @elseif(!empty($promoResult['pesan']))
                <x-alert title="Promo Tidak Valid" description="{{ $promoResult['pesan'] }}" icon="o-x-circle"
                    class="alert-warning mt-4" />
                @endif
            </x-card>
        </div>

        {{-- Kurir section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Kurir" subtitle="Opsional - Kurir jemput dan antar" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kurir Jemput" wire:model="kurirJemputId" icon="o-arrow-up-tray"
                        :options="$kurirOptions" option-value="id" option-label="name"
                        placeholder="Pilih kurir jemput..." hint="Kurir yang menjemput cucian" />

                    <x-select label="Kurir Antar" wire:model="kurirAntarId" icon="o-arrow-down-tray"
                        :options="$kurirOptions" option-value="id" option-label="name"
                        placeholder="Pilih kurir antar..." hint="Kurir yang mengantar cucian" />
                </div>
            </x-card>
        </div>

        {{-- Foto Bukti section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Foto Bukti" subtitle="Opsional - Upload foto bukti" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="space-y-6">
                    <x-image-library wire:model="fotoTimbangan" wire:library="libraryTimbangan"
                        :preview="$libraryTimbangan" label="Foto Bukti Timbangan" hint="Max 5MB per foto"
                        change-text="Ubah" crop-text="Crop" remove-text="Hapus" crop-title-text="Crop Foto"
                        crop-cancel-text="Batal" crop-save-text="Simpan" add-files-text="Tambah Foto" />

                    <x-image-library wire:model="fotoPembayaran" wire:library="libraryPembayaran"
                        :preview="$libraryPembayaran" label="Foto Bukti Pembayaran" hint="Max 5MB per foto"
                        change-text="Ubah" crop-text="Crop" remove-text="Hapus" crop-title-text="Crop Foto"
                        crop-cancel-text="Batal" crop-save-text="Simpan" add-files-text="Tambah Foto" />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('transaksi.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

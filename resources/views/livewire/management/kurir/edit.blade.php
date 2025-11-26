<div>
    <x-header title="Edit Kurir" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi kurir
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas kurir" size="text-lg" />
            </div>

            <x-card class="col-span-3">
                <x-file label="Avatar (Opsional)" wire:model="avatar" accept="image/png, image/jpeg, image/jpg"
                    hint="Ukuran maksimal 2MB. Format: JPG, PNG">
                    <img src="{{ $avatarUrl }}" alt="Avatar" class="h-40 rounded-lg object-cover" />
                </x-file>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kode Kurir" wire:model="formData.kode_kurir" readonly hint="Kode tidak dapat diubah"
                        icon="o-hashtag" />

                    <x-input label="Nama Kurir" wire:model="formData.nama" placeholder="Contoh: Budi Santoso"
                        icon="o-user" required />

                    <x-input label="No. HP" wire:model="formData.no_hp" placeholder="Contoh: 08123456789" icon="o-phone"
                        required />

                    <x-input label="Email" type="email" wire:model="formData.email"
                        placeholder="Contoh: kurir@email.com" icon="o-envelope" hint="Opsional" />

                    <x-password label="Password Baru" wire:model="formData.password"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        hint="Minimal 8 karakter, kosongkan jika tidak ingin mengubah password"
                        password-icon="o-lock-closed" right clearable />

                    <x-password label="Konfirmasi Password Baru" wire:model="formData.password_confirmation"
                        placeholder="Ketik ulang password baru" password-icon="o-lock-closed" right clearable />
                </div>
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-textarea label="Detail Alamat" wire:model="formData.detail_alamat"
                    placeholder="Contoh: Jl. Abunawas No. 123, RT 01/RW 02, Dekat Masjid Al-Ikhlas"
                    hint="Isi dengan: nama jalan, nomor rumah, RT/RW, dan patokan (dekat tempat terkenal/masjid/sekolah)"
                    rows="2" required />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kecamatan" wire:model.live="formData.kecamatan" :options="$kecamatanOptions"
                        placeholder="Pilih kecamatan" icon="o-map-pin" />

                    <x-select label="Kelurahan/Desa" wire:model="formData.kelurahan" :options="$kelurahanOptions"
                        placeholder="Pilih kelurahan"
                        hint="{{ empty($formData['kecamatan']) ? 'Pilih kecamatan dulu' : '' }}" icon="o-map"
                        :disabled="empty($formData['kecamatan'])" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kabupaten/Kota" wire:model="formData.kabupaten_kota" placeholder="Kota Kendari"
                        disabled />

                    <x-input label="Provinsi" wire:model="formData.provinsi" placeholder="Sulawesi Tenggara" disabled />
                </div>
            </x-card>
        </div>

        {{-- Kendaraan section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Kendaraan" subtitle="Data kendaraan kurir" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Jenis Kendaraan" wire:model="formData.jenis_kendaraan" icon="o-truck"
                        :options="$jenisKendaraanOptions" option-value="id" option-label="name"
                        placeholder="Pilih jenis kendaraan" />

                    <x-input label="No. Kendaraan/Plat" wire:model="formData.no_kendaraan"
                        placeholder="Contoh: DT 1234 AB" icon="o-identification" />
                </div>
            </x-card>
        </div>

        {{-- Data Bank section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Data Bank" subtitle="Informasi rekening untuk penggajian (opsional)" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Bank" wire:model="formData.bank_name" placeholder="Contoh: BRI, BCA, Mandiri"
                        icon="o-building-library" />

                    <x-input label="Nomor Rekening" wire:model="formData.bank_account_number"
                        placeholder="Contoh: 1234567890" icon="o-credit-card" />

                    <div class="md:col-span-2">
                        <x-input label="Nama Pemilik Rekening" wire:model="formData.bank_account_name"
                            placeholder="Sesuai buku tabungan" icon="o-user" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Kontak Darurat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Kontak Darurat" subtitle="Kontak yang dapat dihubungi (opsional)" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Kontak Darurat" wire:model="formData.emergency_contact_name"
                        placeholder="Contoh: Ani (Istri)" icon="o-user" />

                    <x-input label="No. HP Kontak Darurat" wire:model="formData.emergency_contact_phone"
                        placeholder="Contoh: 08123456789" icon="o-phone" />

                    <div class="md:col-span-2">
                        <x-input label="Hubungan" wire:model="formData.emergency_contact_relation"
                            placeholder="Contoh: Istri, Suami, Orang Tua" icon="o-heart" />
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Status section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status" subtitle="Status dan tanggal bergabung" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime label="Tanggal Bergabung" wire:model="formData.tanggal_bergabung" icon="o-calendar"
                        required type="datetime-local" />

                    <x-select label="Status" wire:model="formData.status" icon="o-check-circle"
                        :options="$statusOptions" option-value="id" option-label="name" required />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('kurir.index') }}" wire:navigate />
            <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

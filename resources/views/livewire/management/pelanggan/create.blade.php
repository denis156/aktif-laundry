<div>
    <x-header title="Tambah Pelanggan Baru" separator progress-indicator>
        <x-slot:subtitle>
            Tambahkan pelanggan baru ke sistem
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Dasar section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Dasar" subtitle="Data identitas pelanggan" size="text-lg" />
            </div>

            <x-card class="col-span-3">
                <x-file label="Avatar (Opsional)" wire:model="avatar" accept="image/png, image/jpeg, image/jpg"
                    hint="Ukuran maksimal 2MB. Format: JPG, PNG">
                    <img src="{{ asset('images/Logo.png') }}" class="h-40 rounded-lg" />
                </x-file>

                <x-input label="Kode Pelanggan" wire:model="formData.kode_pelanggan" placeholder="Auto Generate"
                    readonly hint="Kode dibuat otomatis" icon="o-hashtag" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Pelanggan" wire:model="formData.nama" placeholder="Contoh: Ahmad Rizki"
                        icon="o-user" required />

                    <x-input label="No. HP" wire:model="formData.no_hp" placeholder="Contoh: 08123456789" icon="o-phone"
                        required />

                    <x-input label="Email" type="email" wire:model="formData.email"
                        placeholder="Contoh: pelanggan@email.com" icon="o-envelope" hint="Opsional" />

                    <x-password label="Password" wire:model="formData.password"
                        placeholder="Minimal 8 karakter" hint="Opsional - untuk login aplikasi customer"
                        password-icon="o-lock-closed" password-visible-icon="o-lock-open" clearable />

                    <x-password label="Konfirmasi Password" wire:model="password_confirmation"
                        placeholder="Ketik ulang password" password-icon="o-lock-closed"
                        password-visible-icon="o-lock-open" clearable />
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
                    placeholder="Jalan, nomor rumah, RT/RW" rows="2" hint="Contoh: Jl. Merdeka No. 123, RT 01/RW 02"
                    required />

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

        {{-- Status section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Status" subtitle="Status dan tanggal pendaftaran"
                    size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-datetime label="Tanggal Daftar" wire:model="formData.tanggal_daftar" type="datetime-local"
                        icon="o-calendar" required />

                    <x-select label="Status" wire:model="formData.status" icon="o-check-circle" :options="[
                            ['id' => 'Aktif', 'name' => 'Aktif'],
                            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif']
                        ]" option-value="id" option-label="name" required />
                </div>
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('pelanggan.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

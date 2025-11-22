<div>
    <x-header title="Tambah Staf" separator progress-indicator>
        <x-slot:subtitle>
            Tambahkan staf baru ke aplikasi
        </x-slot:subtitle>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Profil section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Profil" subtitle="Data personal staf" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-file wire:model="avatar" label="Avatar (Opsional)"
                    hint="Ukuran maksimal {{ $avatarMaxSizeMB }} MB. Format: JPG, PNG"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ asset('images/Logo.png') }}" class="h-40 rounded-lg" />
                </x-file>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Lengkap" wire:model="name" placeholder="Masukkan nama lengkap" icon="o-user"
                        required />
                    <x-input label="Nomor HP" wire:model="no_hp" placeholder="08xx atau +62xxx" icon="o-phone"
                        hint="Format: +62, 62, 08, atau 8" required />

                    <x-input label="Email" type="email" wire:model="email" placeholder="email@example.com"
                        icon="o-envelope" required />

                    <x-group label="Role Pengguna" wire:model="super_admin" :options="$roleOptions" class="checked:btn-primary!"
                        hint="Pilih role untuk pengguna ini" />

                    <x-password label="Password" wire:model="password"
                        placeholder="Minimal {{ $passwordMinLength }} karakter"
                        hint="Password minimal {{ $passwordMinLength }} karakter" password-icon="o-lock-closed"
                        password-visible-icon="o-lock-open" required />

                    <x-password label="Konfirmasi Password" wire:model="password_confirmation"
                        placeholder="Ketik ulang password" password-icon="o-lock-closed" password-visible-icon="o-lock-open"
                        required />
                </div>
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-textarea label="Detail Alamat" wire:model.live="detail_alamat" placeholder="Jalan, nomor, RT/RW, dll"
                    hint="Detail Alamat seperti jalan, nomor, RT/RW" rows="2" required />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Kecamatan" wire:model.live="kecamatan" :options="$kecamatanOptions" placeholder="Pilih kecamatan"
                        :disabled="empty($kabupaten_kota)" hint="{{ empty($kabupaten_kota) ? 'Pilih kabupaten/kota terlebih dahulu' : '' }}"
                        icon="o-map-pin" required />

                    <x-select label="Kelurahan/Desa" wire:model.live="kelurahan" :options="$kelurahanOptions"
                        placeholder="Pilih kelurahan/desa" :disabled="empty($kecamatan)"
                        hint="{{ empty($kecamatan) ? 'Pilih kecamatan terlebih dahulu' : '' }}" icon="o-map" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Kabupaten/Kota" wire:model="kabupaten_kota" placeholder="Kota Kendari" disabled />

                    <x-input label="Provinsi" wire:model="provinsi" placeholder="Sulawesi Tenggara" disabled />
                </div>
            </x-card>
        </div>

        {{-- Data Kepegawaian section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Data Kepegawaian" subtitle="Jam kerja dan gaji staf" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <div class="grid grid-cols-2 gap-4">
                    <x-datetime label="Jam Masuk" wire:model="jam_masuk" type="time"/>

                    <x-datetime label="Jam Keluar" wire:model="jam_keluar" type="time"/>
                </div>

                <x-input label="Gaji Pokok" type="number" wire:model="gaji"
                    placeholder="Masukkan gaji pokok (opsional)" icon="o-banknotes" hint="Gaji dalam rupiah"
                    min="0" />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Batal" link="{{ route('staf.index') }}" wire:navigate />
            <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>

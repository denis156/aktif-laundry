<div>
    <x-header title="Profil Saya" separator progress-indicator>
        <x-slot:subtitle>
            Kelola informasi profil dan keamanan akun Anda
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button label="Keluar" icon="o-arrow-right-start-on-rectangle" link="{{ route('logout') }}" no-wire-navigate
                class="btn-error" responsive />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Profil section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Profil" subtitle="Perbarui data profil Anda" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-file wire:model="avatar" label="Avatar" hint="Upload foto avatar baru (max {{ $avatarMaxSizeMB }} MB)"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ $currentAvatarUrl ? asset('storage/' . $currentAvatarUrl) : asset('images/Logo.png') }}"
                        class="h-38 rounded-lg" />
                </x-file>

                <x-input label="Nama Lengkap" wire:model.live="name" placeholder="Masukkan nama lengkap" icon="o-user"
                    required />

                <x-input label="Email" type="email" wire:model.live="email" placeholder="email@example.com"
                    icon="o-envelope" required />

                <x-input label="Nomor HP" wire:model.live="no_hp" placeholder="08xx atau +62xxx" icon="o-phone"
                    hint="Format: +62, 62, 08, atau 8" required />
            </x-card>
        </div>

        {{-- Alamat section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Alamat" subtitle="Informasi alamat lengkap" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-textarea label="Detail Alamat" wire:model.live="detail_alamat" placeholder="Jalan, nomor, RT/RW, dll"
                    hint="Detail Alamat seperti jalan, nomor, RT/RW" rows="3" required />

                <x-select label="Provinsi" wire:model="provinsi" :options="$provinsiOptions" disabled />

                <x-select label="Kabupaten/Kota" wire:model.live="kabupaten_kota" :options="$kabupatenKotaOptions"
                    placeholder="Pilih kabupaten/kota" required />

                <x-select label="Kecamatan" wire:model.live="kecamatan" :options="$kecamatanOptions" placeholder="Pilih kecamatan"
                    :disabled="empty($kabupaten_kota)" hint="{{ empty($kabupaten_kota) ? 'Pilih kabupaten/kota terlebih dahulu' : '' }}"
                    required />

                <x-select label="Kelurahan/Desa" wire:model.live="kelurahan" :options="$kelurahanOptions"
                    placeholder="Pilih kelurahan/desa" :disabled="empty($kecamatan)"
                    hint="{{ empty($kecamatan) ? 'Pilih kecamatan terlebih dahulu' : '' }}" required />

                <x-textarea label="Alamat Lengkap" wire:model="alamat"
                    hint="Alamat otomatis di-generate dari data di atas" rows="3" disabled />
            </x-card>
        </div>

        {{-- Ubah Password section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Ubah Password" subtitle="Perbarui password untuk keamanan akun" size="text-lg" />
            </div>
            <x-card class="col-span-3">
                <x-password label="Password Saat Ini" wire:model.live="current_password"
                    placeholder="Masukkan password saat ini" icon="o-lock-closed" right />

                <x-password label="Password Baru" wire:model.live="password" placeholder="Minimal {{ $passwordMinLength }} karakter"
                    hint="Password baru minimal {{ $passwordMinLength }} karakter" icon="o-lock-closed" right />

                <x-password label="Konfirmasi Password Baru" wire:model.live="password_confirmation"
                    placeholder="Ketik ulang password baru" icon="o-lock-closed" right />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Simpan Perubahan" type="submit" icon="o-check" class="btn-primary" spinner="save"
                :disabled="!$hasChanges" />
        </x-slot:actions>
    </x-form>
</div>

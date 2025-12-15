<div>
    <x-header title="Profil Saya" separator progress-indicator>
        <x-slot:subtitle>
            Kelola informasi profil dan keamanan akun Anda
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button label="Keluar" icon="o-arrow-right-start-on-rectangle" @click="$wire.modalKonfirmasiLogout = true"
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
                <x-file wire:model="avatar" label="Avatar"
                    hint="Upload foto avatar baru (max {{ $avatarMaxSizeMB }} MB)"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ $avatarUrl }}" alt="Avatar" class="w-40 h-40 rounded-lg object-cover" />
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
                <x-textarea label="Detail Alamat" wire:model.live="detail_alamat"
                    placeholder="Contoh: Jl. Abunawas No. 123, RT 01/RW 02, Dekat Masjid Al-Ikhlas"
                    hint="Isi dengan: nama jalan, nomor rumah, RT/RW, dan patokan (dekat tempat terkenal/masjid/sekolah)"
                    rows="3" />

                <x-select label="Provinsi" wire:model.live="provinsi" :options="$provinsiOptions"
                    placeholder="Pilih provinsi" icon="o-map" />

                <x-select label="Kabupaten/Kota" wire:model.live="kabupaten_kota" :options="$kabupatenKotaOptions"
                    placeholder="Pilih kabupaten/kota" hint="{{ empty($provinsi) ? 'Pilih provinsi dulu' : '' }}"
                    icon="o-building-office-2" :disabled="empty($provinsi)" />

                <x-select label="Kecamatan" wire:model.live="kecamatan" :options="$kecamatanOptions"
                    placeholder="Pilih kecamatan" :disabled="empty($kabupaten_kota)"
                    hint="{{ empty($kabupaten_kota) ? 'Pilih kabupaten/kota dulu' : '' }}" icon="o-map-pin" />

                <x-select label="Kelurahan/Desa" wire:model.live="kelurahan" :options="$kelurahanOptions"
                    placeholder="Pilih kelurahan/desa" :disabled="empty($kecamatan)"
                    hint="{{ empty($kecamatan) ? 'Pilih kecamatan dulu' : '' }}" icon="o-map" />

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

                <x-password label="Password Baru" wire:model.live="password"
                    placeholder="Minimal {{ $passwordMinLength }} karakter"
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

    {{-- Modal Konfirmasi Logout --}}
    <x-modal wire:model="modalKonfirmasiLogout" title="Konfirmasi Keluar" class="backdrop-blur">
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="o-exclamation-triangle" class="w-16 h-16 text-warning" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin keluar dari sistem?</p>
            <p class="text-center text-sm text-base-content/60">Anda perlu login kembali untuk mengakses aplikasi</p>
        </div>

        <x-slot:actions>
            <div class="flex gap-3 w-full">
                <x-button label="Batal" class="btn-ghost flex-1" @click="$wire.modalKonfirmasiLogout = false" />
                <x-button label="Ya, Keluar" icon="o-arrow-right-start-on-rectangle" class="btn-error flex-1"
                    wire:click="logout" spinner="logout" no-wire-navigate />
            </div>
        </x-slot:actions>
    </x-modal>
</div>

<div class="container mx-auto">
    <x-header title="Pengaturan" subtitle="Kelola tema, profil, dan keamanan akun" icon="iconpark.setting-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 flex flex-col justify-center items-center mb-24">
        {{-- Avatar --}}
        <div class="avatar avatar-placeholder">
            <div class="w-24 ring-primary ring-offset-base-100 ring-2 ring-offset-2 rounded-full">
                <img src="{{ $avatarUrl }}" />
            </div>
        </div>
        <p class="font-bold text-lg">{{ $nama }}</p>

        {{-- Tema --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Tema</p>
            <x-card class="shadow-lg border border-primary">
                <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Mode Gelap</span>
                    <livewire:components.dark-mode-toggle toggle-class="toggle-success toggle-xl" :right="true" />
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Aktifkan mode gelap untuk mengurangi ketegangan mata dan
                    menghemat baterai perangkat</p>
            </x-card>
        </div>

        {{-- Profile --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Profil</p>
            <x-card class="shadow-lg border border-primary">
                <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Kelola Profil</span>
                    <x-button label="Lihat" icon="iconpark.user-o" class="btn-primary btn-sm"
                        link="{{ route('profile.kurir') }}" wire:navigate />
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Perbarui informasi profil, alamat, dan data pribadi Anda</p>
            </x-card>
        </div>

        {{-- Keamanan --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Keamanan</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Ubah Password --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium">Ubah Password</span>
                        <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm"
                            @click="$wire.modalUbahPassword = true" />
                    </div>
                    <div class="divider"></div>
                    {{-- Keluar --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium">Keluar dari Sistem</span>
                        <x-button label="Keluar" icon="iconpark.pushdoor-o" class="btn-error btn-sm"
                            @click="$wire.modalKonfirmasiLogout = true" />
                    </div>
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Kelola keamanan akun termasuk ubah password dan keluar dari
                    sistem</p>
            </x-card>
        </div>
    </div>

    {{-- Modal Ubah Password --}}
    <x-modal wire:model="modalUbahPassword" title="Ubah Password" class="modal-bottom w-full backdrop-blur" persistent>
        <x-form wire:submit="savePassword" no-separator>
            <x-password label="Password Lama" wire:model="current_password" placeholder="Masukkan password lama"
                icon="o-lock-closed" required right />

            <x-password label="Password Baru" wire:model="password"
                placeholder="Minimal {{ $passwordMinLength }} karakter" icon="o-lock-closed" required right />

            <x-password label="Konfirmasi Password Baru" wire:model="password_confirmation"
                placeholder="Ketik ulang password baru" icon="o-lock-closed" required right />

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" class="btn-ghost" @click="$wire.modalUbahPassword = false" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="savePassword" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Konfirmasi Logout --}}
    <x-modal wire:model="modalKonfirmasiLogout" title="Konfirmasi Keluar" class="modal-bottom w-full backdrop-blur"
        persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.pushdoor-o" class="w-16 h-16 text-error" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin keluar dari sistem?</p>
            <p class="text-center text-sm text-base-content/60">Anda perlu login kembali untuk mengakses aplikasi</p>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" class="btn-ghost" @click="$wire.modalKonfirmasiLogout = false" />
                <x-button label="Ya, Keluar" class="btn-error" wire:click="logout"
                    spinner="logout" no-wire-navigate />
            </div>
        </x-slot:actions>
    </x-modal>

</div>


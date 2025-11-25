<div class="container mx-auto">
    <x-header title="Pengaturan" subtitle="Kelola tema, notifikasi, profil, dan keamanan akun" icon="iconpark.setting-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 flex flex-col justify-center items-center mb-24">
        {{-- Avatar --}}
        <div class="avatar avatar-online avatar-placeholder">
            <div class="w-24 ring-primary ring-offset-base-100 ring-2 ring-offset-2 rounded-full">
                <img src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp" />
            </div>
        </div>
        <p class="font-bold text-lg">Endors Hehehe</p>

        {{-- Tema --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Tema</p>
            <x-card class="shadow-lg border border-primary">
                <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Mode Gelap</span>
                    <livewire:component.dark-mode-toggle toggle-class="toggle-success toggle-xl" :right="true" />
                </div>
                <div class="divider"></div>
                {{-- <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Mode Gelap (Swap)</span>
                    <livewire:component.dark-mode-swap swap-class="swap-rotate" icon-size="h-8 w-8" />
                </div>
                <div class="divider"></div> --}}
                <p class="text-sm text-base-content/60">Aktifkan mode gelap untuk mengurangi ketegangan mata dan
                    menghemat baterai perangkat</p>
            </x-card>
        </div>

        {{-- Notifikasi --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Notifikasi</p>
            <x-card class="shadow-lg border border-primary">
                <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Notif Pesanan</span>
                    <x-toggle class="toggle-success toggle-xl" right />
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Terima notifikasi instan saat ada pesanan laundry yang perlu
                    dijemput atau diantar</p>
            </x-card>
        </div>

        {{-- Profile --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Pengaturan Profil</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Nama --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium truncate">Endors Hehehe</span>
                        <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm"
                            @click="$wire.modalUbahNama = true" />
                    </div>
                    <div class="divider"></div>
                    {{-- No Telepon --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium truncate">+62 88888888889</span>
                        <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
                    </div>
                    <div class="divider"></div>
                    {{-- Email --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium truncate">endorse@example.com</span>
                        <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
                    </div>
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Perbarui nama, telepon, dan email untuk komunikasi lancar dengan
                    pelanggan</p>
            </x-card>
        </div>

        {{-- Keamanan --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Keamanan</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Ubah Sandi --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium">Ubah Sandi</span>
                        <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
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
                <p class="text-sm text-base-content/60">Kelola keamanan akun termasuk ubah sandi dan keluar dari sistem
                </p>
            </x-card>
        </div>
    </div>


    {{-- Modal Ubah Nama --}}
    <x-modal wire:model="modalUbahNama" title="Ubah Nama" subtitle="Isi form dibawah untuk mengubah nama kamu"
        class="modal-bottom w-full backdrop-blur">
        <x-form no-separator>
            <x-input placeholder="Masukan nama lengkapmu" />

            {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
            <x-slot:actions>
                <x-button label="Simpan" class="btn-primary btn-block" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Konfirmasi Logout --}}
    <x-modal wire:model="modalKonfirmasiLogout" title="Konfirmasi Keluar" class="modal-bottom w-full backdrop-blur">
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.info-o" class="w-16 h-16 text-warning" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin keluar dari sistem?</p>
            <p class="text-center text-sm text-base-content/60">Anda perlu login kembali untuk mengakses aplikasi</p>
        </div>

        <x-slot:actions>
            <div class="flex gap-3 w-full">
                <x-button label="Batal" class="btn-ghost flex-1" @click="$wire.modalKonfirmasiLogout = false" />
                <x-button label="Ya, Keluar" icon="iconpark.pushdoor-o" class="btn-error flex-1" wire:click="logout"
                    spinner="logout" no-wire-navigate />
            </div>
        </x-slot:actions>
    </x-modal>
</div>


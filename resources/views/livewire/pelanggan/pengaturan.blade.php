<div class="container mx-auto">
    <x-header title="Pengaturan" subtitle="Kelola tema, notifikasi, profil, dan keamanan akun" icon="iconpark.setting-o"
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
                    <livewire:component.dark-mode-toggle toggle-class="toggle-success toggle-xl" :right="true" />
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Aktifkan mode gelap untuk mengurangi ketegangan mata dan
                    menghemat baterai perangkat</p>
            </x-card>
        </div>

        {{-- Izin --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Izin</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Notifikasi --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium">Notifikasi</span>
                        <x-toggle
                            class="toggle-success toggle-xl"
                            x-data="{
                                isGranted: false,
                                async init() {
                                    await this.$nextTick();
                                    // Wait for store to be ready
                                    const checkStore = setInterval(() => {
                                        if (Alpine.store('permissions')) {
                                            clearInterval(checkStore);
                                            this.isGranted = Alpine.store('permissions').notification;
                                            this.$watch('$store.permissions.notification', value => {
                                                this.isGranted = value;
                                                $wire.call('syncNotifikasiStatus', value);
                                            });
                                        }
                                    }, 50);
                                },
                                async toggle() {
                                    // Get the NEW value after toggle
                                    const newValue = !this.isGranted;

                                    if (newValue) {
                                        // User wants to enable - request permission first
                                        const result = await Alpine.store('permissions').requestNotification();

                                        if (result.granted) {
                                            // Permission granted - update the toggle
                                            Alpine.store('permissions').notification = true;
                                            // Show test notification
                                            const notification = new Notification('Notifikasi Aktif!', {
                                                body: 'Anda akan menerima notifikasi untuk pesanan laundry Anda',
                                                icon: '{{ asset('images/Logo.png') }}'
                                            });
                                        } else {
                                            // Permission denied - keep toggle off
                                            alert(result.error || 'Izin notifikasi ditolak');
                                        }
                                    } else {
                                        // User wants to disable - just turn off
                                        Alpine.store('permissions').notification = false;
                                    }
                                }
                            }"
                            x-model="isGranted"
                            @click.prevent="toggle()"
                            right />
                    </div>
                    <div class="divider"></div>
                    {{-- Lokasi --}}
                    <div class="flex justify-between items-center">
                        <span class="text-md font-medium">Lokasi</span>
                        <x-toggle
                            class="toggle-success toggle-xl"
                            x-data="{
                                isGranted: false,
                                async init() {
                                    await this.$nextTick();
                                    // Wait for store to be ready
                                    const checkStore = setInterval(() => {
                                        if (Alpine.store('permissions')) {
                                            clearInterval(checkStore);
                                            this.isGranted = Alpine.store('permissions').location;
                                            this.$watch('$store.permissions.location', value => {
                                                this.isGranted = value;
                                                $wire.call('syncLokasiStatus', value);
                                            });
                                        }
                                    }, 50);
                                },
                                async toggle() {
                                    // Get the NEW value after toggle
                                    const newValue = !this.isGranted;

                                    if (newValue) {
                                        // User wants to enable - request permission first
                                        const result = await Alpine.store('permissions').requestLocation();

                                        if (result.granted) {
                                            // Permission granted - update the toggle
                                            Alpine.store('permissions').location = true;
                                        } else {
                                            // Permission denied - keep toggle off
                                            alert(result.error || 'Izin lokasi ditolak');
                                        }
                                    } else {
                                        // User wants to disable - just turn off
                                        Alpine.store('permissions').location = false;
                                    }
                                }
                            }"
                            x-model="isGranted"
                            @click.prevent="toggle()"
                            right />
                    </div>
                </div>
                <div class="divider"></div>
                <p class="text-sm text-base-content/60">Berikan izin notifikasi dan lokasi untuk pengalaman terbaik</p>
            </x-card>
        </div>

        {{-- Profile --}}
        <div class="w-full space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Profil</p>
            <x-card class="shadow-lg border border-primary">
                <div class="flex justify-between items-center">
                    <span class="text-md font-medium">Kelola Profil</span>
                    <x-button label="Lihat" icon="iconpark.user-o" class="btn-primary btn-sm"
                        link="{{ route('profile.pelanggan') }}" wire:navigate />
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
                    <x-button label="Simpan" type="submit" class="btn-warning" spinner="savePassword" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Konfirmasi Logout --}}
    <x-modal wire:model="modalKonfirmasiLogout" title="Konfirmasi Keluar" class="modal-bottom w-full backdrop-blur"
        persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.info-o" class="w-16 h-16 text-warning" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin keluar dari sistem?</p>
            <p class="text-center text-sm text-base-content/60">Anda perlu login kembali untuk mengakses aplikasi</p>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" class="btn-ghost" @click="$wire.modalKonfirmasiLogout = false" />
                <x-button label="Ya, Keluar" icon="iconpark.pushdoor-o" class="btn-error" wire:click="logout"
                    spinner="logout" no-wire-navigate />
            </div>
        </x-slot:actions>
    </x-modal>

</div>

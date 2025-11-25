<div class="container mx-auto">
    <x-header title="Profil Saya" subtitle="Kelola informasi profil dan data pribadi Anda" icon="iconpark.user-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        {{-- Avatar & Nama --}}
        <x-card class="shadow-lg border border-primary">
            <div class="flex flex-col items-center space-y-4">
                <div class="avatar avatar-online avatar-placeholder">
                    <div class="w-24 ring-primary ring-offset-base-100 ring-2 ring-offset-2 rounded-full">
                        <img src="{{ $avatarUrl ?? asset('images/Logo.png') }}" />
                    </div>
                </div>
                <div class="text-center">
                    <p class="font-bold text-xl">{{ $nama ?? 'Pelanggan' }}</p>
                    <p class="text-sm text-base-content/60">{{ $kodePelanggan }}</p>
                </div>
                <x-button label="Ubah Foto" icon="iconpark.camera-o" class="btn-primary btn-sm" />
            </div>
        </x-card>

        {{-- Informasi Kontak --}}
        <div class="space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Informasi Kontak</p>
            <x-card class="shadow-lg border border-primary">
                <div class="space-y-4">
                    {{-- Nama --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Nama Lengkap</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium">{{ $nama ?? '-' }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
                        </div>
                    </div>
                    <div class="divider"></div>
                    {{-- No HP --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">No. Telepon</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium">{{ $noHp ?? '-' }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
                        </div>
                    </div>
                    <div class="divider"></div>
                    {{-- Email --}}
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Email</p>
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium truncate">{{ $email ?? '-' }}</span>
                            <x-button label="Ubah" icon="iconpark.write-o" class="btn-success btn-sm" />
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Alamat --}}
        <div class="space-y-2">
            <p class="font-bold text-base-content/60 text-md ml-2">Alamat</p>
            <x-card class="shadow-lg border border-primary">
                <div>
                    <p class="text-xs text-base-content/60 mb-2">Alamat Lengkap</p>
                    <p class="text-md">{{ $alamat ?? 'Belum ada alamat' }}</p>
                </div>
                <div class="divider"></div>
                <x-button label="Ubah Alamat" icon="iconpark.write-o" class="btn-success btn-sm btn-block" />
            </x-card>
        </div>
    </div>
</div>

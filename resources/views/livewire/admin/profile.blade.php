<div>
    <x-header title="Profil Saya" separator progress-indicator>
        <x-slot:subtitle>
            Kelola informasi profil dan keamanan akun Anda
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button label="Keluar" icon="o-arrow-right-start-on-rectangle" link="{{ route('logout') }}" no-wire-navigate class="btn-error" responsive />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="save" no-separator>
        {{-- Informasi Profil section --}}
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Informasi Profil" subtitle="Perbarui data profil Anda" size="text-lg" />
            </div>
            <x-card class="col-span-3 grid gap-3">
                <x-file wire:model="avatar" label="Avatar" hint="Upload foto avatar baru (max 2MB)"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ $currentAvatarUrl ? asset('storage/' . $currentAvatarUrl) : asset('images/Logo.png') }}"
                        class="h-40 rounded-lg" />
                </x-file>

                <x-input label="Nama Lengkap" wire:model.live="name" placeholder="Masukkan nama lengkap" icon="o-user"
                    required />

                <x-input label="Email" type="email" wire:model.live="email" placeholder="email@example.com"
                    icon="o-envelope" required />
            </x-card>
        </div>

        {{-- Ubah Password section --}}
        <div class="lg:grid grid-cols-5 mt-8">
            <div class="col-span-2">
                <x-header title="Ubah Password" subtitle="Perbarui password untuk keamanan akun" size="text-lg" />
            </div>
            <x-card class="col-span-3 grid gap-3">
                <x-password label="Password Saat Ini" wire:model.live="current_password"
                    placeholder="Masukkan password saat ini" password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open" />

                <x-password label="Password Baru" wire:model.live="password" placeholder="Minimal 8 karakter"
                    hint="Password baru minimal 8 karakter" password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open" />

                <x-password label="Konfirmasi Password Baru" wire:model.live="password_confirmation"
                    placeholder="Ketik ulang password baru" password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open" />
            </x-card>
        </div>

        <x-slot:actions>
            <x-button label="Simpan Perubahan" type="submit" icon="o-check" class="btn-primary" spinner="save"
                :disabled="!$hasChanges" />
        </x-slot:actions>
    </x-form>
</div>

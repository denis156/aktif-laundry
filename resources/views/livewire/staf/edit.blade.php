<div>
    <x-header title="Edit Staf" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi staf
        </x-slot:subtitle>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-file
                    wire:model="avatar"
                    label="Avatar"
                    hint="Opsional - Upload foto avatar baru (max 2MB)"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ $currentAvatarUrl ? asset('storage/' . $currentAvatarUrl) : asset('images/Logo.png') }}" class="h-40 rounded-lg" />
                </x-file>

                <x-input
                    label="Nama Lengkap"
                    wire:model="name"
                    placeholder="Masukkan nama lengkap"
                    icon="o-user"
                    required />

                <x-input
                    label="Email"
                    type="email"
                    wire:model="email"
                    placeholder="email@example.com"
                    icon="o-envelope"
                    required />

                <x-group
                    label="Role Pengguna"
                    wire:model="super_admin"
                    :options="$roleOptions"
                    class="checked:btn-primary!"
                    hint="Pilih role untuk pengguna ini" />

                <div class="divider my-6">Ubah Password (Opsional)</div>

                <x-password
                    label="Password Baru"
                    wire:model="password"
                    placeholder="Kosongkan jika tidak ingin mengubah"
                    hint="Minimal 8 karakter, kosongkan jika tidak ingin mengubah password"
                    password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open"
                    clearable />

                <x-password
                    label="Konfirmasi Password Baru"
                    wire:model="password_confirmation"
                    placeholder="Ketik ulang password baru"
                    password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open"
                    clearable />
            </div>

            <x-slot:actions>
                <x-button label="Batal" link="{{ route('staf.index') }}" wire:navigate />
                <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>

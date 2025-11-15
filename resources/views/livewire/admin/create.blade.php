<div>
    <x-header title="Tambah User" separator progress-indicator>
        <x-slot:subtitle>
            Tambahkan pengguna baru ke aplikasi
        </x-slot:subtitle>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-file
                    wire:model="avatar"
                    label="Avatar"
                    hint="Opsional - Upload foto avatar (max 2MB)"
                    accept="image/png, image/jpeg, image/jpg">
                    <img src="{{ asset('images/Logo.png') }}" class="h-40 rounded-lg" />
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

                <div class="divider my-4">Password</div>

                <x-password
                    label="Password"
                    wire:model="password"
                    placeholder="Minimal 8 karakter"
                    hint="Password minimal 8 karakter"
                    password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open"
                    required />

                <x-password
                    label="Konfirmasi Password"
                    wire:model="password_confirmation"
                    placeholder="Ketik ulang password"
                    password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open"
                    required />
            </div>

            <x-slot:actions>
                <x-button label="Batal" link="{{ route('admin.index') }}" wire:navigate />
                <x-button label="Simpan" type="submit" icon="o-check" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>

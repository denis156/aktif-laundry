<div>
    <x-header title="Edit User" separator progress-indicator>
        <x-slot:subtitle>
            Perbarui informasi pengguna
        </x-slot:subtitle>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="space-y-4">
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
                <x-button label="Batal" link="{{ route('admin.index') }}" wire:navigate />
                <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>

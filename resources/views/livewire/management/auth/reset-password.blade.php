<div>
    <x-card class="shadow-xl bg-base-100" body-class="border-t border-dashed">

        <x-slot:figure>
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="w-auto h-34 mt-6" />
        </x-slot:figure>

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-base-content">Reset Password</h2>
            <p class="text-lg text-base-content/60 mt-2">
                Masukkan password baru untuk akun Anda
            </p>
        </div>

        <!-- Reset Password Form -->
        <x-form wire:submit="resetPassword">
            <div class="space-y-5">
                <x-input label="Email" type="email" wire:model="email" placeholder="Email Anda" icon="o-envelope"
                    readonly disabled />

                <x-password label="Password Baru" wire:model="password" placeholder="Masukkan password baru"
                    icon="o-lock-closed" hint="Minimal 8 karakter" right required />

                <x-password label="Konfirmasi Password" wire:model="password_confirmation"
                    placeholder="Masukkan ulang password baru" icon="o-lock-closed"
                    hint="Harus sama dengan password baru" right required />
            </div>

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" link="{{ route('login') }}" class="btn-error btn-block"
                        icon="o-x-circle" />
                    <x-button label="Reset Password" type="submit" spinner="resetPassword" class="btn-success btn-block"
                        icon="o-check" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-base-content/50">
        <p>Developed by <span class="font-bold text-primary">Team {{ config('app.name') }}</span></p>
    </div>
</div>

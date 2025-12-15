<x-card class="bg-base-200" body-class="space-y-6" title="Reset Password"
    subtitle="Buat password baru untuk akun Anda">
    <x-form wire:submit="resetPassword">
        <!-- Email Input -->
        <x-input label="Email" type="email" wire:model="email" placeholder="nama@email.com" icon="o-envelope" required
            class="input-lg" />

        <!-- New Password Input -->
        <x-password label="Password Baru" wire:model="password" placeholder="••••••••" icon="o-lock-closed"
            hint="Minimal 8 karakter" right required class="input-lg" />

        <!-- Confirm Password Input -->
        <x-password label="Konfirmasi Password Baru" wire:model="password_confirmation" placeholder="••••••••"
            icon="o-lock-closed" right required class="input-lg" />

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Reset Password" type="submit" spinner="resetPassword"
                    class="btn-primary btn-lg btn-block" icon="o-check" />

                <x-button label="Kembali ke Login" link="{{ route('login.kurir') }}" class="btn-neutral btn-soft btn-lg btn-block"
                    icon="o-arrow-left" wire:navigate />
            </div>
        </x-slot:actions>
    </x-form>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>Kurir Aktif Developed by <span class="font-bold text-primary uppercase">Team {{ config('app.name') }}</span>
        </p>
    </div>
</x-card>

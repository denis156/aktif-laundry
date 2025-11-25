<x-card class="bg-base-200" body-class="space-y-6" title="Lupa Password"
    subtitle="Masukkan email Anda untuk mendapatkan link reset password">
    <x-form wire:submit="sendResetLink">
        <!-- Email Input -->
        <x-input label="Email" type="email" wire:model="email" placeholder="nama@email.com" icon="o-envelope" required
            autofocus class="input-lg" />

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Kirim Link Reset" type="submit" spinner="sendResetLink"
                    class="btn-primary btn-lg btn-block" icon="o-paper-airplane" />

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

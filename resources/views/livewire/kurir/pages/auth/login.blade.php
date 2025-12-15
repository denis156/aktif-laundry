<x-card class="bg-base-200" body-class="space-y-6" title="Selamat Datang Kembali"
    subtitle="Login untuk melanjutkan layanan pengiriman Anda">
    <x-form wire:submit="login">
        <!-- Email Input -->
        <x-input label="Email" type="email" wire:model="email" placeholder="nama@email.com" icon="o-envelope" required
            autofocus class="input-lg" />

        <!-- Password Input -->
        <x-password label="Password" wire:model="password" placeholder="••••••••" icon="o-lock-closed" right required
            class="input-lg" />

        <!-- Remember & Forgot -->
        <div class="flex items-center justify-between text-sm mt-2">
            <x-checkbox label="Ingat Saya" wire:model="remember" class="checkbox-primary" />
            <a href="{{ route('kurir.password.request') }}" class="link link-primary" wire:navigate>
                Lupa Password?
            </a>
        </div>

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Masuk" type="submit" spinner="login" class="btn-primary btn-lg btn-block"
                    icon="o-arrow-right-end-on-rectangle" />
            </div>
        </x-slot:actions>
    </x-form>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>Kurir Aktif Developed by <span class="font-bold text-primary uppercase">Team {{ config('app.name') }}</span></p>
    </div>
</x-card>

<x-card class="bg-base-200" body-class="space-y-6" title="Selamat Datang Kembali"
    subtitle="Login untuk melanjutkan layanan laundry Anda">
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
            <a href="{{ route('pelanggan.password.request') }}" class="link link-primary" wire:navigate>
                Lupa Password?
            </a>
        </div>

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Masuk" type="submit" spinner="login" class="btn-primary btn-lg btn-block"
                    icon="o-arrow-right-end-on-rectangle" />

                <!-- Link to Register -->
                <div class="text-center text-sm">
                    <span class="text-base-content/60">Belum punya akun?</span>
                    <a href="{{ route('register.pelanggan') }}" class="link link-primary font-semibold" wire:navigate>
                        Daftar di sini
                    </a>
                </div>
            </div>
        </x-slot:actions>
    </x-form>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>{{ config('app.name') }} Developed by <span class="font-bold text-primary uppercase">Team {{ config('app.name') }}</span></p>
    </div>
</x-card>

<x-card class="bg-base-200" body-class="space-y-6" title="Daftar Akun Baru"
    subtitle="Bergabunglah dengan {{ config('app.name') }} untuk layanan laundry terbaik">
    <x-form wire:submit="register">
        <!-- Nama Input -->
        <x-input label="Nama Lengkap" wire:model="nama" placeholder="Masukkan nama lengkap" icon="o-user" required
            autofocus class="input-lg" />

        <!-- Email Input -->
        <x-input label="Email" type="email" wire:model="email" placeholder="nama@email.com" icon="o-envelope" required
            class="input-lg" />

        <!-- No HP Input -->
        <x-input label="Nomor HP" wire:model="no_hp" placeholder="08xx atau +62xxx" icon="o-phone" required
            class="input-lg" hint="Format: +62, 62, 08, atau 8" />

        <!-- Password Input -->
        <x-password label="Password" wire:model="password" placeholder="••••••••" icon="o-lock-closed" right required
            class="input-lg" hint="Minimal 8 karakter" />

        <!-- Password Confirmation Input -->
        <x-password label="Konfirmasi Password" wire:model="password_confirmation" placeholder="••••••••"
            icon="o-lock-closed" right required class="input-lg" />

        <!-- Kode Referral (Optional) -->
        <x-input label="Kode Referral (Opsional)" wire:model="kode_referral" placeholder="Masukkan kode referral"
            icon="o-gift" class="input-lg" hint="Kosongkan jika tidak ada" />

        <!-- Terms & Conditions -->
        <div class="mt-4">
            <x-checkbox wire:model="agree_terms" required>
                <x-slot:label>
                    <span class="text-sm">
                        Saya setuju dengan
                        <a href="#" class="link link-primary">Syarat dan Ketentuan</a>
                        yang berlaku
                    </span>
                </x-slot:label>
            </x-checkbox>
        </div>

        <!-- Submit Button -->
        <x-slot:actions>
            <div class="space-y-3 w-full pt-2">
                <x-button label="Daftar Sekarang" type="submit" spinner="register"
                    class="btn-primary btn-lg btn-block" icon="o-user-plus" />

                <!-- Link to Login -->
                <div class="text-center text-sm">
                    <span class="text-base-content/60">Sudah punya akun?</span>
                    <a href="{{ route('login.pelanggan') }}" class="link link-primary font-semibold" wire:navigate>
                        Masuk di sini
                    </a>
                </div>
            </div>
        </x-slot:actions>
    </x-form>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>{{ config('app.name') }} Developed by <span class="font-bold text-primary uppercase">Team
                {{ config('app.name') }}</span></p>
    </div>
</x-card>

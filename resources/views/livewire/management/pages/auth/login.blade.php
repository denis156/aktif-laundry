<div>
    <x-card class="shadow-xl bg-base-100" body-class="border-t border-dashed">

        <x-slot:figure>
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="w-auto h-34 mt-6" />
        </x-slot:figure>
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <p class="text-lg text-base-content/60 mt-2">
                Sistem Manajemen Data <span class="font-bold text-primary">{{ config('app.name') }}</span>
            </p>
        </div>

        <!-- Login Form -->
        <x-form wire:submit="login">
            <div class="space-y-5">
                <x-input label="Email" type="email" wire:model="email" placeholder="Masukkan email" icon="o-envelope"
                    required autofocus />

                <x-password label="Password" wire:model="password" placeholder="Masukkan password" icon="o-lock-closed"
                    hint="Klik icon untuk melihat password" right required />

                <div class="flex items-center justify-between">
                    <x-checkbox label="Ingat Saya" wire:model="remember" />
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary/80"
                        wire:navigate>
                        Lupa Password?
                    </a>
                </div>
            </div>

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" link="{{ route('landing-page') }}" class="btn-error btn-block"
                        icon="o-x-circle" />
                    <x-button label="Masuk" type="submit" spinner="login" class="btn-success btn-block"
                        icon="o-arrow-right-end-on-rectangle" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-base-content/50">
        <p>Developed by <span class="font-bold text-primary">Team {{ config('app.name') }}</span></p>
    </div>
</div>

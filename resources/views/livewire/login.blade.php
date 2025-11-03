<div>
    <x-card class="shadow-xl">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                <x-icon name="o-home" class="w-10 h-10 text-primary" />
            </div>
            <h1 class="text-3xl font-bold bg-linear-to-r from-primary to-accent bg-clip-text text-transparent">
                Aktif Laundry
            </h1>
            <p class="text-sm text-base-content/60 mt-2">
                Sistem Manajemen Laundry
            </p>
        </div>

        <!-- Login Form -->
        <x-form wire:submit="login">
            <div class="space-y-5">
                <x-input
                    label="Email"
                    type="email"
                    wire:model="email"
                    placeholder="Masukkan email"
                    icon="o-envelope"
                    required
                    autofocus
                />

                <x-password
                    label="Password"
                    wire:model="password"
                    placeholder="Masukkan password"
                    password-icon="o-lock-closed"
                    password-visible-icon="o-lock-open"
                    hint="Klik icon untuk toggle visibility"
                    required
                />

                <x-checkbox label="Remember me" wire:model="remember" />
            </div>

            <x-slot:actions>
                <x-button
                    label="Login"
                    type="submit"
                    spinner="login"
                    class="btn-primary w-full"
                    icon="o-arrow-right-on-rectangle"
                />
            </x-slot:actions>
        </x-form>
    </x-card>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-base-content/50">
        <p>Developed by Denis Djodian Ardika</p>
    </div>
</div>

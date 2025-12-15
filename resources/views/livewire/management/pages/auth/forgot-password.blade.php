<div>
    <x-card class="shadow-xl bg-base-100" body-class="border-t border-dashed">

        <x-slot:figure>
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="w-auto h-34 mt-6" />
        </x-slot:figure>

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-base-content">Lupa Password</h2>
            <p class="text-lg text-base-content/60 mt-2">
                Masukkan email Anda untuk menerima link reset password
            </p>
        </div>

        <!-- Forgot Password Form -->
        <x-form wire:submit="sendResetLink">
            <div class="space-y-5">
                <x-input label="Email" type="email" wire:model="email" placeholder="Masukkan email" icon="o-envelope"
                    required autofocus />
            </div>

            <x-slot:actions>
                <div class="grid grid-cols-2 gap-4 w-full">
                    <x-button label="Batal" link="{{ route('login') }}" class="btn-error btn-block" icon="o-x-circle" />
                    <x-button label="Kirim Link Reset" type="submit" spinner="sendResetLink"
                        class="btn-success btn-block" icon="o-paper-airplane" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-base-content/50">
        <p>Developed by <span class="font-bold text-primary">Team {{ config('app.name') }}</span></p>
    </div>
</div>

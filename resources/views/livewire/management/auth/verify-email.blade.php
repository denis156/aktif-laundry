<div>
    <x-card class="shadow-xl bg-base-100" body-class="border-t border-dashed">

        <x-slot:figure>
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="w-auto h-34 mt-6" />
        </x-slot:figure>

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-base-content">Verifikasi Email</h2>
            <p class="text-lg text-base-content/60 mt-2">
                Verifikasi diperlukan untuk melanjutkan
            </p>
        </div>

        <!-- Verification Info -->
        <div class="space-y-5">
            <x-alert icon="o-information-circle" class="alert-info">
                <div class="space-y-2">
                    <p class="font-semibold">Email verifikasi telah dikirim ke:</p>
                    <p class="text-sm font-mono">{{ $userEmail }}</p>
                    <p class="text-sm mt-3">Silakan cek inbox atau spam folder Anda dan klik link verifikasi yang
                        dikirim.</p>
                </div>
            </x-alert>

            <div class="divider text-base-content/40">ATAU</div>
        </div>
        <x-slot:actions separator>
            <!-- Action Buttons -->
            <div class="grid grid-cols-1 gap-4 w-full">
                <x-button label="Kirim Ulang Email Verifikasi" wire:click="resendVerification"
                    spinner="resendVerification" class="btn-success btn-block" icon="o-paper-airplane" />

                <x-button label="Logout" wire:click="logout" spinner="logout" class="btn-error btn-block"
                    icon="o-arrow-right-on-rectangle" />
            </div>
        </x-slot:actions>
    </x-card>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-base-content/50">
        <p>Developed by <span class="font-bold text-primary">Team {{ config('app.name') }}</span></p>
    </div>
</div>
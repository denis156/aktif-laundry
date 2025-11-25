<x-card class="bg-base-200" body-class="space-y-6" title="Verifikasi Email"
    subtitle="Kami telah mengirimkan email verifikasi ke alamat Anda">

    <!-- Email Display -->
    <div class="bg-base-300 rounded-lg p-4 text-center">
        <p class="text-sm text-base-content/70 mb-1">Email verifikasi dikirim ke:</p>
        <p class="font-semibold text-primary break-all">{{ $userEmail }}</p>
    </div>

    <!-- Info Alert -->
    <x-alert icon="o-information-circle" class="alert-info">
        <span class="text-sm">
            Silakan cek inbox atau spam folder Anda dan klik link verifikasi. Jika tidak menerima email, klik tombol di
            bawah untuk mengirim ulang.
        </span>
    </x-alert>

    <!-- Actions -->
    <div class="space-y-3">
        <x-button label="Kirim Ulang Email Verifikasi" wire:click="resendVerification" spinner="resendVerification"
            class="btn-primary btn-lg btn-block" icon="o-paper-airplane" />

        <x-button label="Logout" wire:click="logout" class="btn-ghost btn-block" icon="o-arrow-right-on-rectangle" />
    </div>

    <!-- Help Text -->
    <div class="bg-base-300 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <x-icon name="o-question-mark-circle" class="w-5 h-5 shrink-0 mt-0.5 text-base-content/70" />
            <div class="text-sm space-y-2">
                <p class="font-medium">Belum menerima email?</p>
                <ul class="list-disc list-inside text-base-content/70 text-xs space-y-1">
                    <li>Periksa folder spam atau junk email</li>
                    <li>Pastikan alamat email yang dimasukkan sudah benar</li>
                    <li>Tunggu beberapa menit kemudian coba kirim ulang</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs">
        <p>Kurir Aktif Developed by <span class="font-bold text-primary uppercase">Team {{ config('app.name') }}</span>
        </p>
    </div>
</x-card>

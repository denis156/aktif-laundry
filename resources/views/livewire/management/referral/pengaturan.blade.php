<div class="p-6">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Konfigurasi Referral</h1>
                <p class="text-base-content/70 mt-1">Atur promo default untuk sistem referral</p>
            </div>
            <x-button label="Kembali" icon="o-arrow-left" link="/management/referral" class="btn-ghost" />
        </div>

        @if(!$isReferralEnabled)
        <x-alert icon="o-exclamation-triangle" class="alert-warning shadow-md">
            <span class="font-semibold">Sistem Referral Nonaktif</span>
            <span class="text-sm">Aktifkan sistem referral di halaman <a href="/management/pengaturan"
                    class="link link-warning font-medium" wire:navigate>Pengaturan</a> untuk menggunakan fitur
                ini.</span>
        </x-alert>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- LEFT SIDE: Form (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Info Card --}}
                <x-card class="shadow-md bg-info/5 border border-info/20">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0">
                            <x-icon name="o-information-circle" class="w-6 h-6 text-info" />
                        </div>
                        <div class="flex-1 space-y-2 text-sm">
                            <p class="font-semibold text-info">Cara Kerja Sistem Referral:</p>
                            <ul class="list-disc list-inside space-y-1 text-base-content/70">
                                <li><span class="font-medium">Auto-generate:</span> Kode referral dibuat otomatis saat
                                    pelanggan register</li>
                                <li><span class="font-medium">Referrer:</span> Pelanggan yang mengajak (pemilik kode)
                                </li>
                                <li><span class="font-medium">Referee:</span> Pelanggan baru yang pakai kode referral
                                </li>
                                <li><span class="font-medium">Promo otomatis:</span> Promo yang dipilih di bawah akan
                                    otomatis terhubung ke setiap kode referral baru</li>
                            </ul>
                        </div>
                    </div>
                </x-card>

                {{-- Configuration Card --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <x-icon name="o-gift" class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Promo Default</h3>
                            <p class="text-sm text-base-content/70">Pilih promo yang akan digunakan untuk sistem
                                referral</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <x-select label="Promo untuk Referrer (Yang Mengajak)" wire:model="default_promo_referrer_id"
                            :options="$promoOptions" placeholder="Pilih promo (opsional)" icon="o-user-plus"
                            hint="Promo yang diberikan ke yang mengajak saat referral berhasil digunakan" inline />

                        <x-select label="Promo untuk Referee (Yang Diajak)" wire:model="default_promo_referee_id"
                            :options="$promoOptions" placeholder="Pilih promo (opsional)" icon="o-user"
                            hint="Promo yang diberikan ke pendaftar baru yang menggunakan kode referral" inline />
                    </div>
                </x-card>

            </div>

            {{-- RIGHT SIDE: Preview & Actions --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">

                    {{-- Preview Card --}}
                    <x-card class="shadow-lg bg-primary text-primary-content">
                        <div class="text-center space-y-3">
                            <div class="w-16 h-16 mx-auto bg-white/20 rounded-full flex items-center justify-center">
                                <x-icon name="o-eye" class="w-8 h-8" />
                            </div>
                            <h3 class="text-xl font-bold">Preview Konfigurasi</h3>
                            <div class="divider divider-neutral opacity-20"></div>
                            <div class="text-left space-y-3 bg-white/10 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Promo Referrer:</span>
                                    <span class="font-bold text-sm">
                                        @if($default_promo_referrer_id)
                                        {{ collect($promoOptions)->firstWhere('id', $default_promo_referrer_id)['name']
                                        ?? 'Tidak diketahui' }}
                                        @else
                                        <span class="opacity-60">Belum dipilih</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="divider divider-neutral opacity-10 my-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-80">Promo Referee:</span>
                                    <span class="font-bold text-sm">
                                        @if($default_promo_referee_id)
                                        {{ collect($promoOptions)->firstWhere('id', $default_promo_referee_id)['name']
                                        ?? 'Tidak diketahui' }}
                                        @else
                                        <span class="opacity-60">Belum dipilih</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    {{-- Action Buttons --}}
                    <x-card class="shadow-md">
                        <div class="space-y-3">
                            <x-button label="Simpan Konfigurasi" icon="o-check" wire:click="save" spinner="save"
                                class="btn-success w-full" />
                            @if($hasChanges)
                            <x-button label="Reset Perubahan" icon="o-arrow-path" wire:click="resetChanges"
                                class="btn-warning btn-outline w-full" />
                            @endif
                        </div>
                    </x-card>

                    {{-- Help Card --}}
                    <x-card class="shadow-md bg-base-200">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-question-mark-circle" class="w-5 h-5 text-accent" />
                                <h4 class="font-semibold">Bantuan</h4>
                            </div>
                            <div class="space-y-2 text-sm text-base-content/70">
                                <p><span class="font-medium">Kosongkan</span> jika tidak ingin memberikan promo otomatis
                                </p>
                                <p><span class="font-medium">Promo</span> bisa diubah manual per referral di halaman
                                    Edit</p>
                            </div>
                        </div>
                    </x-card>

                </div>
            </div>
        </div>

    </div>
</div>

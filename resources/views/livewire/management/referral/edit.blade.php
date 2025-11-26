<div class="p-6">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Edit Referral</h1>
                <p class="text-base-content/70 mt-1">Edit promo dan tracking untuk kode referral</p>
            </div>
            <x-button label="Kembali" icon="o-arrow-left" link="/management/referral" class="btn-ghost" />
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- LEFT SIDE: Form (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Read-only Info Card --}}
                <x-card class="shadow-md bg-base-200/50">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-info/10 p-2 rounded-lg">
                            <x-icon name="o-information-circle" class="w-6 h-6 text-info" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Informasi Referral</h3>
                            <p class="text-sm text-base-content/70">Data dasar (tidak dapat diubah)</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <p class="text-sm text-base-content/60">Kode Referral</p>
                            <p class="font-mono font-bold text-lg text-primary">{{ $kode_referral }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-base-content/60">Pemilik</p>
                            <p class="font-semibold">{{ $pelanggan_nama }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-base-content/60">Total Penggunaan</p>
                            <p class="font-bold text-lg">{{ $total_referral }} kali</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-base-content/60">Total Berhasil</p>
                            <p class="font-bold text-lg text-success">{{ $total_berhasil }} transaksi</p>
                        </div>
                    </div>

                    @if($total_referral > 0)
                    <div class="mt-4 pt-4 border-t border-base-300">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-base-content/60">Conversion Rate</span>
                            <span class="font-bold text-lg">
                                {{ $total_referral > 0 ? round(($total_berhasil / $total_referral) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <progress class="progress progress-success w-full mt-2"
                            value="{{ $total_referral > 0 ? ($total_berhasil / $total_referral) * 100 : 0 }}"
                            max="100"></progress>
                    </div>
                    @endif
                </x-card>

                {{-- Editable Promo Card --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <x-icon name="o-gift" class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Promo Reward</h3>
                            <p class="text-sm text-base-content/70">Atur promo untuk referrer dan referee</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <x-select
                            label="Promo untuk Referrer (Yang Mengajak)"
                            wire:model="promo_referrer_id"
                            :options="$promoOptions"
                            placeholder="Pilih promo (opsional)"
                            icon="o-user-plus"
                            hint="Promo yang diberikan ke yang mengajak saat referral berhasil" />

                        <x-select
                            label="Promo untuk Referee (Yang Diajak)"
                            wire:model="promo_referee_id"
                            :options="$promoOptions"
                            placeholder="Pilih promo (opsional)"
                            icon="o-user"
                            hint="Promo yang diberikan ke pendaftar baru yang pakai kode" />
                    </div>
                </x-card>

                {{-- Campaign Tracking Card --}}
                <x-card class="shadow-md">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-base-300">
                        <div class="bg-accent/10 p-2 rounded-lg">
                            <x-icon name="o-chart-bar" class="w-6 h-6 text-accent" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Campaign Tracking</h3>
                            <p class="text-sm text-base-content/70">Track sumber campaign (opsional)</p>
                        </div>
                    </div>

                    <x-input
                        label="Campaign Source"
                        wire:model="campaign_source"
                        placeholder="Misal: instagram, facebook, whatsapp"
                        icon="o-megaphone"
                        hint="Sumber campaign untuk tracking dan analitik (maksimal 100 karakter)" />
                </x-card>

            </div>

            {{-- RIGHT SIDE: Actions --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">

                    {{-- Action Card --}}
                    <x-card class="shadow-lg">
                        <div class="space-y-3">
                            <x-button label="Simpan Perubahan" icon="o-check" wire:click="save" spinner="save"
                                class="btn-success w-full" />
                            <x-button label="Batal" icon="o-x-mark" link="/management/referral"
                                class="btn-outline w-full" />
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
                                <p><span class="font-medium">Kode & Pemilik</span> tidak dapat diubah</p>
                                <p><span class="font-medium">Promo</span> bisa dikosongkan jika tidak diperlukan</p>
                                <p><span class="font-medium">Campaign Source</span> berguna untuk tracking ROI</p>
                            </div>
                        </div>
                    </x-card>

                </div>
            </div>
        </div>

    </div>
</div>

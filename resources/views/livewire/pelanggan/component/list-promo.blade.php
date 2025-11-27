<div class="w-full space-y-2">
    <div class="flex justify-between items-center">
        <h1 class="text-lg font-bold text-base-content/80 uppercase">Promo Spesial</h1>
    </div>

    @if ($promoList->isEmpty())
        <div class="w-full py-8 text-center">
            <x-icon name="iconpark.ticket-o" class="w-16 h-16 mx-auto text-base-content/20" />
            <p class="text-base-content/60 mt-2">Belum ada promo tersedia</p>
        </div>
    @else
        <div class="w-full space-x-4 flex overflow-x-auto snap-x snap-mandatory no-scrollbar">
            @foreach ($promoList as $promo)
                @php
                    $nilaiDiskonFormatted = \App\Helper\Database\PromoHelper::formatNilaiDiskon(
                        $promo->tipe_diskon,
                        $promo->nilai_diskon,
                    );
                    $berlakuUntukLabel = match ($promo->berlaku_untuk) {
                        'semua' => 'Semua Pelanggan',
                        'pelanggan_baru' => 'Pelanggan Baru',
                        'layanan_tertentu' => 'Layanan Tertentu',
                        default => $promo->berlaku_untuk,
                    };
                @endphp

                <x-card title="{{ $promo->nama_promo }}" subtitle="{{ $promo->kode_promo }}"
                    class="shadow-lg border border-b-5 border-r-5 border-info w-[78dvw] flex-none snap-start">
                    <div class="space-y-2">
                        {{-- Nilai Diskon --}}
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.tag-o" class="w-5 h-5 text-info" />
                            <span class="text-base font-semibold text-info">{{ $nilaiDiskonFormatted }}</span>
                        </div>

                        {{-- Deskripsi --}}
                        @if ($promo->deskripsi)
                            <p class="text-sm text-base-content/80">{{ $promo->deskripsi }}</p>
                        @endif

                        {{-- Min Transaksi --}}
                        @if ($promo->min_transaksi)
                            <div class="flex items-center gap-2">
                                <x-icon name="iconpark.wallet-o" class="w-4 h-4 text-base-content/60" />
                                <span class="text-xs text-base-content/60">Min. Rp
                                    {{ number_format($promo->min_transaksi, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- Min/Max Berat --}}
                        @if ($promo->min_berat || $promo->max_berat)
                            <div class="flex items-center gap-2">
                                <x-icon name="iconpark.scale-o" class="w-4 h-4 text-base-content/60" />
                                <span class="text-xs text-base-content/60">
                                    @if ($promo->min_berat && $promo->max_berat)
                                        {{ $promo->min_berat }}kg - {{ $promo->max_berat }}kg
                                    @elseif ($promo->min_berat)
                                        Min. {{ $promo->min_berat }}kg
                                    @else
                                        Max. {{ $promo->max_berat }}kg
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Berlaku Untuk --}}
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.people-o" class="w-4 h-4 text-base-content/60" />
                            <span class="text-xs text-base-content/60">{{ $berlakuUntukLabel }}</span>
                        </div>

                        {{-- Tanggal Berakhir --}}
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.time-o" class="w-4 h-4 text-warning" />
                            <span class="text-xs text-base-content/60">Berlaku s/d
                                {{ $promo->tanggal_berakhir->format('d M Y') }}</span>
                        </div>

                        {{-- Kuota --}}
                        @if ($promo->kuota_total)
                            <div class="flex items-center gap-2">
                                <x-icon name="iconpark.ticket-o" class="w-4 h-4 text-success" />
                                <span class="text-xs text-base-content/60">Tersisa
                                    {{ $promo->kuota_total - $promo->kuota_terpakai }} kuota</span>
                            </div>
                        @endif

                        {{-- Auto Apply Badge --}}
                        @if ($promo->auto_apply)
                            <div class="badge badge-success badge-sm">
                                <x-icon name="iconpark.magic-o" class="w-3 h-3 mr-1" />
                                Otomatis Terapkan
                            </div>
                        @endif
                    </div>

                    <x-slot:figure>
                        <div class="border-b-2 border-dashed border-info aspect-3/2 w-full bg-base-200">
                            <img src="{{ $this->getBannerUrl($promo) }}" alt="{{ $promo->nama_promo }}"
                                class="w-full h-full object-cover" />
                        </div>
                    </x-slot:figure>

                    <x-slot:menu>
                        <x-button icon="iconpark.shareone-o" class="btn-circle btn-sm btn-info btn-soft" />
                    </x-slot:menu>

                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            @if ($promo->terms_conditions)
                                <x-button label="Detail" class="btn-sm btn-soft btn-neutral"
                                    wire:click="$dispatch('show-promo-detail', { id: {{ $promo->id }} })" />
                            @else
                                <x-button label="Detail" class="btn-sm btn-soft btn-neutral" disabled />
                            @endif
                            <x-button label="Gunakan" class="btn-sm btn-info"
                                wire:click="$dispatch('use-promo', { kode: '{{ $promo->kode_promo }}' })" />
                        </div>
                    </x-slot:actions>
                </x-card>
            @endforeach
        </div>
    @endif
</div>

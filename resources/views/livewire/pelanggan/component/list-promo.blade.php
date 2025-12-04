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
                @endphp

                <x-card title="{{ $promo->nama_promo }}" subtitle="{{ $promo->kode_promo }}"
                    class="shadow-lg border border-b-5 border-r-5 border-info w-[78dvw] flex-none snap-start">
                    {{-- Deskripsi --}}
                    @if ($promo->deskripsi)
                        <p class="text-sm text-base-content/80">{{ $promo->deskripsi }}</p>
                    @endif

                    <x-slot:figure>
                        <div class="border-b-2 border-dashed border-info aspect-3/2 w-full bg-base-200">
                            <img src="{{ $this->getBannerUrl($promo) }}" alt="{{ $promo->nama_promo }}"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='{{ asset('images/Logo.png') }}';" />
                        </div>
                    </x-slot:figure>

                    <x-slot:actions separator>
                        <div class="w-full grid grid-cols-2 gap-2">
                            <x-button label="Detail" class="btn-sm btn-soft btn-neutral"
                                link="{{ route('detail-promo.pelanggan', $promo->id) }}" />
                            <x-button label="Gunakan" class="btn-sm btn-info"
                                wire:click="usePromo({{ $promo->id }})" spinner="usePromo" />
                        </div>
                    </x-slot:actions>
                </x-card>
            @endforeach
        </div>
    @endif
</div>

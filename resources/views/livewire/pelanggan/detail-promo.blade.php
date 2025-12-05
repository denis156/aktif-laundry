<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Detail Promo" subtitle="{{ $promo->nama_promo }} - {{ $promo->kode_promo }}" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('beranda.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    <!-- Single Card dengan Semua Informasi -->
    <x-card class="w-full shadow-lg border border-info mb-34" body-class="space-y-4">
        <x-slot:figure>
            <div class="border-b-2 border-dashed border-info aspect-3/2 w-full bg-base-200 relative">
                <img src="{{ $this->getBannerUrl($promo) }}" alt="{{ $promo->nama_promo }}"
                    class="w-full h-full object-cover" />

                <!-- Overlay Info di Atas Gambar -->
                <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent flex items-end p-6">
                    <div class="text-white">
                        <div class="text-4xl font-bold mb-1">{{ $this->getNilaiDiskonFormatted() }}</div>
                        <div class="text-sm opacity-90">{{ $this->getMasaBerlakuFormatted() }} • Kuota: {{
                            $this->getKuotaTersedia() }}</div>
                    </div>
                </div>
            </div>
        </x-slot:figure>

        <!-- Header Info -->
        <div class="text-center">
            <div class="text-2xl font-bold text-info mb-2">{{ $promo->nama_promo }}</div>
            <div class="text-base-content/60">Kode: {{ $promo->kode_promo }}</div>
        </div>

        <!-- Informasi Detail -->
        <!-- Target Pelanggan -->
        <div class="flex items-start">
            <x-icon name="iconpark.info-o" class="w-5 h-5 mr-3 mt-0.5 text-primary" />
            <div>
                <div class="font-medium">Berlaku Untuk</div>
                <div class="text-sm text-base-content/70">{{ $this->getBerlakuUntukLabel() }}</div>
            </div>
        </div>

        <!-- Minimal Pembelian (jika ada) -->
        @if($this->getMinimalPembelian())
        <div class="flex items-start">
            <x-icon name="iconpark.shoppingcart-o" class="w-5 h-5 mr-3 mt-0.5 text-primary" />
            <div>
                <div class="font-medium">Minimal Pembelian</div>
                <div class="text-sm text-base-content/70">{{ $this->getMinimalPembelian() }}</div>
            </div>
        </div>
        @endif

        <!-- Status Penggunaan -->
        <div class="flex items-start">
            <x-icon name="iconpark.checkone-o"
                class="w-5 h-5 mr-3 mt-0.5 {{ $this->canUsePromo() ? 'text-success' : 'text-error' }}" />
            <div>
                <div class="font-medium">Status Penggunaan</div>
                <div class="text-sm {{ $this->canUsePromo() ? 'text-success' : 'text-error' }}">
                    {{ $this->getStatusPenggunaan() }}
                </div>
            </div>
        </div>

        <div class="divider font-semibold">Syarat & Ketentuan</div>
        <!-- Syarat & Ketentuan -->
        @if($promo->deskripsi || $promo->terms_conditions)
        @if($promo->deskripsi)
        <div>
            <div class="font-medium mb-2">Deskripsi</div>
            <div class="text-sm text-base-content/70 whitespace-pre-line">{{ $promo->deskripsi }}</div>
        </div>
        @endif

        @if($promo->terms_conditions)
        <div>
            <div class="font-medium mb-2">Ketentuan</div>
            <div class="text-sm text-base-content/70 whitespace-pre-line">{{ $promo->terms_conditions }}</div>
        </div>
        @endif
        @endif
    </x-card>

    <div class="fab mb-18">
        <x-button wire:click="usePromo" class="btn-info btn-md" label="{{ $this->getActionButtonLabel() }}" :disabled="!$this->canUsePromo()" spinner="usePromo" />
    </div>
</div>

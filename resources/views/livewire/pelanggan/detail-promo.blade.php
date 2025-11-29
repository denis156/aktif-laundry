<div class="container mx-auto pb-48">
    <x-header title="Detail Promo" subtitle="{{ $promo->nama_promo }}" icon="iconpark.ticket-o"
        icon-classes="bg-info text-info-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('beranda.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    @if ($promo)
    <div class="space-y-4">

        {{-- Banner Image --}}
        <div class="w-full aspect-video bg-base-200 rounded-xl overflow-hidden shadow-lg">
            <img src="{{ $this->getBannerUrl() }}" alt="{{ $promo->nama_promo }}" class="w-full h-full object-cover" />
        </div>

        {{-- Promo Info Card --}}
        <x-card title="Informasi Promo" subtitle="Detail informasi promo yang tersedia"
            class="shadow-lg border border-primary">
            <div class="space-y-4">
                {{-- Nama & Kode --}}
                <div>
                    <h2 class="text-xl font-bold text-base-content">{{ $promo->nama_promo }}</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <x-icon name="iconpark.ticket-o" class="w-4 h-4 text-info" />
                        <span class="text-sm text-base-content/60 font-mono">{{ $promo->kode_promo }}</span>
                    </div>
                </div>

                {{-- Nilai Diskon --}}
                <div class="flex items-center gap-3 p-4 bg-info/10 rounded-lg">
                    <x-icon name="iconpark.tag-o" class="w-8 h-8 text-info" />
                    <div>
                        <p class="text-xs text-base-content/60">Potongan Harga</p>
                        <span class="text-2xl font-bold text-info">{{ $this->getNilaiDiskonFormatted() }}</span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if ($promo->deskripsi)
                <div>
                    <h3 class="font-semibold text-base-content mb-2 flex items-center gap-2">
                        <x-icon name="iconpark.info-o" class="w-5 h-5 text-base-content/60" />
                        Deskripsi
                    </h3>
                    <p class="text-sm text-base-content/80 bg-base-200/50 p-3 rounded-lg">{{ $promo->deskripsi }}</p>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Detail & Syarat Card --}}
        <x-card title="Detail & Syarat Ketentuan" subtitle="Syarat dan ketentuan berlaku"
            class="shadow-lg border border-primary">
            <div class="space-y-6">
                {{-- Detail Grid --}}
                <div class="grid grid-cols-1 gap-4">
                    {{-- Min Transaksi --}}
                    @if ($promo->min_transaksi)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.wallet-o" class="w-5 h-5 text-base-content/60" />
                            <span class="text-sm font-medium">Minimum Transaksi</span>
                        </div>
                        <span class="text-sm font-bold">Rp {{ number_format($promo->min_transaksi, 0, ',', '.')
                            }}</span>
                    </div>
                    @endif

                    {{-- Min/Max Berat --}}
                    @if ($promo->min_berat || $promo->max_berat)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.scale-o" class="w-5 h-5 text-base-content/60" />
                            <span class="text-sm font-medium">Berat Laundry</span>
                        </div>
                        <span class="text-sm font-bold">
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
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.user-o" class="w-5 h-5 text-base-content/60" />
                            <span class="text-sm font-medium">Berlaku Untuk</span>
                        </div>
                        <span class="text-sm font-bold">{{ $this->getBerlakuUntukLabel() }}</span>
                    </div>

                    {{-- Tanggal Berakhir --}}
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.time-o" class="w-5 h-5 text-warning" />
                            <span class="text-sm font-medium">Berlaku Sampai</span>
                        </div>
                        <span class="text-sm font-bold">{{ $promo->tanggal_berakhir->format('d M Y') }}</span>
                    </div>

                    {{-- Kuota --}}
                    @if ($promo->kuota_total)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.ticket-o" class="w-5 h-5 text-success" />
                            <span class="text-sm font-medium">Kuota Tersisa</span>
                        </div>
                        <span class="text-sm font-bold">{{ $promo->kuota_total - $promo->kuota_terpakai }} kuota</span>
                    </div>
                    @endif

                    {{-- Max per user --}}
                    @if ($promo->max_per_user)
                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <x-icon name="iconpark.user-o" class="w-5 h-5 text-base-content/60" />
                            <span class="text-sm font-medium">Maksimal per User</span>
                        </div>
                        <span class="text-sm font-bold">{{ $promo->max_per_user }}x pakai</span>
                    </div>
                    @endif
                </div>

                {{-- Syarat & Ketentuan --}}
                @if ($promo->terms_conditions)
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-base-content mb-3 flex items-center gap-2">
                        <x-icon name="iconpark.info-o" class="w-5 h-5 text-base-content/60" />
                        Syarat & Ketentuan
                    </h4>
                    <div class="text-sm text-base-content/80 space-y-2 bg-base-200/30 p-4 rounded-lg">
                        {!! nl2br(e($promo->terms_conditions)) !!}
                    </div>
                </div>
                @endif

                {{-- Auto Apply Info --}}
                @if ($promo->auto_apply)
                <div class="flex items-center gap-3 p-4 bg-success/10 rounded-lg border border-success/20">
                    <x-icon name="iconpark.checkone-o" class="w-6 h-6 text-success" />
                    <div>
                        <p class="text-sm font-medium text-success">Promo Otomatis</p>
                        <p class="text-xs text-success/80">Promo ini akan diterapkan secara otomatis pada transaksi yang
                            memenuhi syarat</p>
                    </div>
                </div>
                @endif
            </div>
        </x-card>

    </div>

    {{-- Fixed Bottom Button --}}
    <div class="fixed bottom-20 left-0 right-0 z-30 px-4 pb-4">
        <div class="container mx-auto max-w-2xl">
            <x-button label="Gunakan Promo Ini" class="btn-info btn-lg w-full shadow-2xl" wire:click="usePromo"
                spinner="usePromo" />
        </div>
    </div>
</div>
@endif
</div>

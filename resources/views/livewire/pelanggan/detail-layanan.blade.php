<div class="container mx-auto pb-48">
    <x-header title="Detail Layanan" subtitle="{{ $layanan->nama }}" icon="iconpark.localpin-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('pesanan.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    @if ($layanan)
    <div class="space-y-4">

        {{-- Icon & Info Card --}}
        <x-card title="Informasi Layanan" subtitle="Detail informasi layanan laundry"
            class="shadow-lg border border-primary">
            <div class="space-y-4">
                {{-- Icon & Nama --}}
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-primary/10 rounded-xl flex items-center justify-center">
                        <img src="{{ $this->getIconUrl() }}" alt="{{ $layanan->nama }}"
                            class="w-12 h-12 object-contain" />
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-base-content">{{ $layanan->nama }}</h2>
                        @if ($layanan->slug)
                        <p class="text-sm text-base-content/60 font-mono">{{ $layanan->slug }}</p>
                        @endif
                    </div>
                </div>

                {{-- Harga --}}
                <div class="flex items-center gap-3 p-4 bg-primary/10 rounded-lg">
                    <x-icon name="iconpark.papermoney-o" class="w-8 h-8 text-primary" />
                    <div>
                        <p class="text-xs text-base-content/60">Harga Layanan</p>
                        <span class="text-2xl font-bold text-primary">{{ $this->getHargaFormatted() }}</span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if ($layanan->deskripsi)
                <div>
                    <h3 class="font-semibold text-base-content mb-2 flex items-center gap-2">
                        <x-icon name="iconpark.info-o" class="w-5 h-5 text-base-content/60" />
                        Deskripsi Layanan
                    </h3>
                    <p class="text-sm text-base-content/80 bg-base-200/50 p-3 rounded-lg">{{ $layanan->deskripsi }}</p>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Detail Layanan Card --}}
        <x-card title="Informasi Detail" subtitle="Spesifikasi lengkap layanan" class="shadow-lg border border-primary">
            <div class="space-y-4">
                {{-- Kategori --}}
                @if ($layanan->kategori)
                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="iconpark.tag-o" class="w-5 h-5 text-base-content/60" />
                        <span class="text-sm font-medium">Kategori</span>
                    </div>
                    <span class="text-sm font-bold capitalize">{{ $layanan->kategori }}</span>
                </div>
                @endif

                {{-- Satuan Harga --}}
                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="iconpark.scale-o" class="w-5 h-5 text-base-content/60" />
                        <span class="text-sm font-medium">Satuan Harga</span>
                    </div>
                    <span class="text-sm font-bold">Per {{ ucfirst($layanan->satuan_harga) }}</span>
                </div>

                {{-- Waktu Estimasi --}}
                @if ($layanan->estimasi_hari)
                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="iconpark.time-o" class="w-5 h-5 text-base-content/60" />
                        <span class="text-sm font-medium">Estimasi Selesai</span>
                    </div>
                    <span class="text-sm font-bold">{{ $layanan->estimasi_hari }} Hari</span>
                </div>
                @endif

                {{-- Status --}}
                <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="iconpark.checkone-o" class="w-5 h-5 text-base-content/60" />
                        <span class="text-sm font-medium">Status</span>
                    </div>
                    <div class="badge {{ $layanan->status === 'Aktif' ? 'badge-success' : 'badge-error' }} badge-sm">
                        {{ $layanan->status }}
                    </div>
                </div>
            </div>

            {{-- Catatan Khusus --}}
            @if ($layanan->catatan)
            <div class="border-t pt-4 mt-6">
                <h4 class="font-semibold text-base-content mb-3 flex items-center gap-2">
                    <x-icon name="iconpark.file-text-o" class="w-5 h-5 text-base-content/60" />
                    Catatan Khusus
                </h4>
                <div class="text-sm text-base-content/80 space-y-2 bg-base-200/30 p-4 rounded-lg">
                    {!! nl2br(e($layanan->catatan)) !!}
                </div>
            </div>
            @endif
        </x-card>

        {{-- Features/Keunggulan --}}
        @if ($layanan->features)
        <x-card title="Keunggulan Layanan" class="shadow-lg">
            <div class="grid grid-cols-1 gap-3">
                @foreach(explode("\n", $layanan->features) as $feature)
                @if(trim($feature))
                <div class="flex items-center gap-3 p-3 bg-success/5 rounded-lg">
                    <x-icon name="iconpark.check-o" class="w-5 h-5 text-success" />
                    <span class="text-sm text-base-content/80">{{ trim($feature) }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </x-card>
        @endif

        {{-- Fixed Bottom Button --}}
        <div class="fixed bottom-20 left-0 right-0 z-30 px-4 pb-4">
            <div class="container mx-auto max-w-2xl">
                <x-button label="Pesan Layanan Ini" class="btn-primary btn-lg w-full shadow-2xl"
                    wire:click="pesanSekarang" spinner="pesanSekarang" />
            </div>
        </div>
    </div>
    @endif
</div>

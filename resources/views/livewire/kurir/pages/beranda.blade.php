<div class="container mx-auto">
    <div class="space-y-4 flex flex-col justify-center items-center mb-24">
        <x-card class="shadow-lg w-full border border-primary"
            body-class="h-18 flex flex-col justify-center items-center">
            <x-slot:figure class="flex justify-between h-28 border-b border-dashed">
                <div class="p-4 space-y-auto">
                    <h1 class="text-sm text-base-content/60 font-medium">Total Pengiriman</h1>
                    <span class="text-5xl text-success font-bold">{{ number_format($this->totalPengiriman) }}</span>
                </div>
                <div class="p-4">
                    <x-button link="{{ route('rute.kurir') }}" class="btn-primary btn-soft border border-primary rounded-lg h-18 w-14"
                        icon="iconpark.maproadtwo-o" />
                </div>
            </x-slot:figure>
            <div class="grid grid-cols-3 gap-8 w-full">
                <div class="flex flex-col items-center space-y-1">
                    <x-button link="{{ route('aktifitas.kurir') }}" class="btn-circle btn-soft btn-lg btn-warning border-warning" icon="iconpark.listview-o" />
                    <span class="text-xs text-base-content/60 font-light">Aktifitas</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button link="{{ route('profile.kurir') }}" class="btn-circle btn-soft btn-lg btn-info border-info" icon="iconpark.user-o" />
                    <span class="text-xs text-base-content/60 font-light">Profile</span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <x-button link="{{ route('pengaturan.kurir') }}" class="btn-circle btn-soft btn-lg btn-success border-success" icon="iconpark.setting-o" />
                    <span class="text-xs text-base-content/60 font-light">Pengaturan</span>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-2 gap-2 w-full z-5">
            <div class="stats shadow-lg border border-b-4 border-r-4 border-success col-span-full">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <x-icon name="iconpark.checkone-o" class="inline-block h-8 w-8 text-success" />
                    </div>
                    <div class="stat-title text-xs">Pesanan Selesai</div>
                    <div class="stat-value text-sm text-success">{{ number_format($this->totalSelesai) }}</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-b-4 border-r-4 border-error">
                <div class="stat">
                    <div class="stat-title text-xs">Antar</div>
                    <div class="stat-value text-sm text-error">{{ number_format($this->totalAntar) }}</div>
                </div>
            </div>
            <div class="stats shadow-lg border border-b-4 border-r-4 border-warning">
                <div class="stat">
                    <div class="stat-title">Jemput</div>
                    <div class="stat-value text-sm text-warning">{{ number_format($this->totalJemput) }}</div>
                </div>
            </div>
        </div>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/60">Pesanan Aktif</h1>
                <x-button label="Lihat Semua" link="{{ route('rute.kurir') }}" class="btn-link btn-xs btn-primary"/>
            </div>

            @if ($this->pengantaranAktif->isEmpty())
            <x-card class="shadow-lg border border-primary"
                body-class="flex flex-col items-center justify-center py-8 space-y-3">
                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center">
                    <x-icon name="iconpark.maproadtwo-o" class="h-8 text-base-content/40" />
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-base font-bold text-base-content">Belum Ada Pesanan Aktif</h3>
                    <p class="text-xs text-base-content/60 max-w-md">
                        Pesanan aktif akan muncul di sini
                    </p>
                </div>
            </x-card>
            @else
            @foreach ($this->pengantaranAktif as $transaksi)
            @php
            $avatarUrl = $this->getTransaksiAvatar($transaksi);

            // Calculate total weight or items
            $totalBerat = $transaksi->total_berat > 0 ? number_format($transaksi->total_berat, 1) . ' kg' : null;
            $totalItem = $transaksi->total_item > 0 ? $transaksi->total_item . ' pcs' : null;
            $quantity = $totalBerat ?? $totalItem ?? '-';
            @endphp

            <a href="{{ route('rute-detail.kurir', $transaksi->id) }}" wire:navigate class="block">
                <x-card wire:key="aktif-{{ $transaksi->id }}"
                    title="{{ $transaksi->kode_transaksi }}"
                    subtitle="{{ $transaksi->tanggal_masuk->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }}"
                    class="shadow-lg border border-b-4 border-r-4 border-primary active:border-0 active:shadow-sm transition-all cursor-pointer">
                    <x-slot:menu>
                        <x-avatar :image="$avatarUrl" class="w-10 h-10 rounded-full shrink-0" />
                    </x-slot:menu>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-base-content">{{ $transaksi->nama_pelanggan }}</span>
                            </div>
                            @if ($transaksi->pelanggan?->alamat)
                            <div class="flex items-start gap-1 mt-1">
                                <span class="text-xs text-base-content/60 line-clamp-2">{{ $transaksi->pelanggan->alamat }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1 items-end shrink-0">
                            @if ($transaksi->status === 'Proses')
                            <x-badge value="Jemput" class="badge-warning badge-sm" />
                            @elseif ($transaksi->status === 'Selesai')
                            <x-badge value="Antar" class="badge-success badge-sm" />
                            @endif
                        </div>
                    </div>
                </x-card>
            </a>
            @endforeach
            @endif
        </div>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/60">Aktifitas Terbaru</h1>
                <x-button label="Lihat Semua" link="{{ route('aktifitas.kurir') }}" class="btn-link btn-xs btn-primary"/>
            </div>

            @if ($this->transaksiTerbaru->isEmpty())
            <x-card class="shadow-lg border border-primary"
                body-class="flex flex-col items-center justify-center py-12 space-y-4">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                    <x-icon name="iconpark.delivery-o" class="h-10 text-base-content/40" />
                </div>
                <div class="text-center space-y-2">
                    <h3 class="text-lg font-bold text-base-content">Belum Ada Aktifitas</h3>
                    <p class="text-sm text-base-content/60 max-w-md">
                        Aktifitas terbaru akan muncul di sini
                    </p>
                </div>
            </x-card>
            @else
            @foreach ($this->transaksiTerbaru as $transaksi)
            @php
            $firstLayanan = $transaksi->transaksiLayanan->first();
            $totalLayanan = $transaksi->transaksiLayanan->count();

            $avatarUrl = $this->getTransaksiAvatar($transaksi);

            // Status badge styling
            $badgeClass = \App\Helper\Database\TransaksiHelper::getStatusBadgeClass($transaksi->status);

            // Calculate total weight or items
            $totalBerat = $transaksi->total_berat > 0 ? number_format($transaksi->total_berat, 1) . ' kg' : null;
            $totalItem = $transaksi->total_item > 0 ? $transaksi->total_item . ' pcs' : null;
            $quantity = $totalBerat ?? $totalItem ?? '-';
            @endphp

            <a href="{{ route('detail-aktifitas.kurir', $transaksi->id) }}" wire:navigate class="block">
                <x-card wire:key="transaksi-{{ $transaksi->id }}" title="{{ $transaksi->kode_transaksi }}"
                    subtitle="{{ $transaksi->tanggal_masuk->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }}"
                    class="shadow-lg border border-b-4 border-r-4 border-primary active:border-0 active:shadow-sm transition-all cursor-pointer">
                    <x-slot:menu>
                        <x-avatar :image="$avatarUrl" class="w-10 h-10 rounded-full shrink-0" />
                    </x-slot:menu>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($firstLayanan)
                                <span class="text-xs text-primary font-medium">{{ $firstLayanan->nama_layanan }}</span>
                                @if ($totalLayanan > 1)
                                <span class="text-xs text-base-content/40">+{{ $totalLayanan - 1 }} lainnya</span>
                                @endif
                                <span class="text-xs text-base-content/40">•</span>
                                @endif
                                <span class="text-xs text-base-content/60">{{ $quantity }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm font-semibold text-base-content">{{ $transaksi->nama_pelanggan
                                    }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1 items-end shrink-0">
                            <x-badge :value="$transaksi->status" class="{{ $badgeClass }} badge-sm" />
                        </div>
                    </div>
                </x-card>
            </a>
            @endforeach
            @endif
        </div>

        <div>

        </div>
    </div>
</div>

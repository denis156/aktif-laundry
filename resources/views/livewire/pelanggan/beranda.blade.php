<div class="container mx-auto" wire:poll.visible.10s>
    <div class="w-full space-y-8 flex flex-col justify-center items-center mb-24">
        <x-card class="w-full shadow-lg border border-primary">
            <x-slot:figure class="flex justify-between h-28 border-b border-secondary border-dashed">
                <div class="p-4 space-y-auto">
                    <h1 class="text-sm text-base-content/60 font-medium">Total Transaksi</h1>
                    <span class="text-5xl text-success font-bold">{{ $totalTransaksi }}</span>
                </div>
                <div class="p-4">
                    <x-button class="btn-primary btn-soft border border-primary rounded-lg h-18 w-14"
                        icon="iconpark.plus-o" link="{{ route('pesanan.pelanggan') }}" />
                </div>
            </x-slot:figure>
            <div class="stats stats-vertical shadow-lg w-full border border-b-4 border-r-4 border-secondary">
                <div class="stat bg-info/84 text-base-content">
                    <div class="stat-figure">
                        <x-icon name="iconpark.close-o" class="h-6 text-base-content" />
                    </div>
                    <div class="stat-title text-xs font-bold text-base-content">Proses</div>
                    <div class="stat-value text-sm font-bold text-base-content">{{ $transaksiProses }}</div>
                    <div class="stat-desc text-base-content">Pesananmu yang diproses</div>
                </div>
                <div class="stat bg-success/84 text-base-content">
                    <div class="stat-figure">
                        <x-icon name="iconpark.checkone-o" class="h-6 text-base-content" />
                    </div>
                    <div class="stat-title text-xs font-bold text-base-content">Selesai</div>
                    <div class="stat-value text-sm font-bold text-base-content">{{ $transaksiSelesai }}</div>
                    <div class="stat-desc text-base-content">Pesananmu yang selesai</div>
                </div>
            </div>
        </x-card>

        {{-- list layanan --}}
        <livewire:pelanggan.component.list-layanan>

        {{-- list promo --}}
        <livewire:pelanggan.component.list-promo>

        {{-- card referral --}}
        <livewire:pelanggan.component.card-referral>

        <div class="w-full space-y-2">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/80 uppercase">Pesanan Terbaru</h1>
                <x-button label="Lihat Semua" class="btn-ghost btn-xs btn-primary" wire:navigate
                    href="{{ route('riwayat.pelanggan') }}" />
            </div>

            @if ($transaksiTerbaru->isEmpty())
                <div class="text-center py-8">
                    <x-icon name="iconpark.handlex-o" class="w-16 h-16 mx-auto text-base-content/40" />
                    <p class="text-sm text-base-content/60 mt-2">Belum ada pesanan</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($transaksiTerbaru as $transaksi)
                        @php
                            $firstLayanan = $transaksi->transaksiLayanan->first();
                            $totalLayanan = $transaksi->transaksiLayanan->count();

                            // Avatar dengan prioritas: kurir antar > kurir jemput > pelanggan
                            $avatarUrl = $this->getTransaksiAvatar($transaksi);

                            // Status badge styling
                            $badgeClass = \App\Helper\Database\TransaksiHelper::getStatusBadgeClass($transaksi->status);

                            // Calculate total weight or items
                            $totalBerat = $transaksi->total_berat > 0 ? number_format($transaksi->total_berat, 1) . ' kg' : null;
                            $totalItem = $transaksi->total_item > 0 ? $transaksi->total_item . ' pcs' : null;
                            $quantity = $totalBerat ?? $totalItem ?? '-';
                        @endphp

                        <a href="{{ route('detail-pesanan.pelanggan', $transaksi->id) }}" wire:navigate class="block">
                            <x-card wire:key="transaksi-{{ $transaksi->id }}"
                                title="{{ $transaksi->kode_transaksi }}"
                                subtitle="{{ $transaksi->tanggal_masuk->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }}"
                                class="shadow-lg border border-b-4 border-r-4 border-primary active:border-0 active:shadow-sm transition-all cursor-pointer">
                                <x-slot:menu>
                                    <x-avatar :image="$avatarUrl" class="w-10 h-10 rounded-full shrink-0" />
                                </x-slot:menu>

                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex flex-col flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            @if ($firstLayanan)
                                                <span
                                                    class="text-xs text-primary font-medium">{{ $firstLayanan->nama_layanan }}</span>
                                                @if ($totalLayanan > 1)
                                                    <span class="text-xs text-base-content/40">+{{ $totalLayanan - 1 }}
                                                        lainnya</span>
                                                @endif
                                                <span class="text-xs text-base-content/40">•</span>
                                            @endif
                                            <span class="text-xs text-base-content/60">{{ $quantity }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-sm font-semibold text-base-content">Rp
                                                {{ number_format($transaksi->total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <x-badge :value="$transaksi->status" class="{{ $badgeClass }} badge-sm shrink-0" />
                                </div>
                            </x-card>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

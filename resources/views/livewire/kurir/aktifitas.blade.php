<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Aktifitas" subtitle="Lihat semua pesanan jemput dan antar Anda" icon="iconpark.listview-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        @if ($this->transaksiGrouped->isEmpty())
        <x-card class="shadow-lg border border-primary"
            body-class="flex flex-col items-center justify-center py-12 space-y-4">
            <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                <x-icon name="iconpark.listview-o" class="h-10 text-base-content/40" />
            </div>
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-base-content">Belum Ada Aktifitas</h3>
                <p class="text-sm text-base-content/60 max-w-md">
                    Aktifitas jemput dan antar Anda akan muncul di sini
                </p>
            </div>
        </x-card>
        @else
        @foreach ($this->transaksiGrouped as $group)
        <div class="w-full space-y-2" wire:key="period-{{ $loop->index }}">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold text-base-content/60">{{ $group['period'] }}</h1>
            </div>

            @foreach ($group['transaksi'] as $transaksi)
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
        </div>
        @endforeach

        @if (!$showAll && ($limit > 10 || $this->hasMoreData))
        <div class="grid grid-cols-2 w-full gap-4">
            @if ($limit > 10)
            <x-button wire:click="loadLess" label="Lebih Sedikit" icon="iconpark.aligntexttop-o"
                class="btn-secondary {{ $this->hasMoreData ? 'col-span-1' : 'col-span-2' }}" />
            @endif
            @if ($this->hasMoreData)
            <x-button wire:click="loadMore" label="Lebih Banyak" icon="iconpark.aligntextbottom-o"
                class="btn-primary {{ $limit > 10 ? 'col-span-1' : 'col-span-2' }}" />
            @endif
        </div>
        @endif
        @endif
    </div>
</div>

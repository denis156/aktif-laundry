<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Rute" subtitle="Lihat dan kelola rute pengiriman untuk pesanan" icon="iconpark.maproadtwo-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator />

    <div class="space-y-4 mb-24">
        {{-- Maps Lokasi --}}
        <livewire:kurir.component.lokasi-saya />

        @if ($this->transaksiRute->isEmpty())
            {{-- Info Tidak Ada Pesanan --}}
            <x-card class="shadow-lg border border-primary"
                body-class="flex flex-col items-center justify-center py-12 space-y-4">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                    <x-icon name="iconpark.maproadtwo-o" class="h-10 text-base-content/40" />
                </div>
                <div class="text-center space-y-2">
                    <h3 class="text-lg font-bold text-base-content">Belum Ada Pesanan Aktif</h3>
                    <p class="text-sm text-base-content/60 max-w-md">
                        Rute pengiriman akan muncul saat ada pesanan yang sedang kamu kerjakan
                    </p>
                </div>
            </x-card>
        @else
            {{-- List Pesanan Untuk Rute --}}
            <div class="space-y-2">
                <h2 class="text-lg font-bold text-base-content/60 ml-2">Pesanan Aktif ({{ $this->transaksiRute->count() }})</h2>

                @foreach ($this->transaksiRute as $transaksi)
                    @php
                        $avatarUrl = $this->getTransaksiAvatar($transaksi);

                        // Calculate total weight or items
                        $totalBerat = $transaksi->total_berat > 0 ? number_format($transaksi->total_berat, 1) . ' kg' : null;
                        $totalItem = $transaksi->total_item > 0 ? $transaksi->total_item . ' pcs' : null;
                        $quantity = $totalBerat ?? $totalItem ?? '-';
                    @endphp

                    <a href="{{ route('rute-detail.kurir', $transaksi->id) }}" wire:navigate class="block">
                        <x-card wire:key="rute-{{ $transaksi->id }}"
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
            </div>
        @endif
    </div>
</div>

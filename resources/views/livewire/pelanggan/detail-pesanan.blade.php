<div class="container mx-auto">
    <x-header title="{{ $transaksi->kode_transaksi }}"
        subtitle="{{ $transaksi->tanggal_masuk->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }}" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('riwayat.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    <div class="space-y-4 mb-24">
        {{-- Informasi Pesanan --}}
        <x-card title="Pesanan" subtitle="Detail pesanan laundry Anda" class="shadow-lg border border-primary w-full"
            body-class="space-y-4">
            <x-slot:menu>
                {{-- Status Pesanan --}}
                <x-badge :value="$transaksi->status" class="badge-sm {{ $this->getStatusBadgeClass() }} truncate" />
            </x-slot:menu>

            {{-- Detail Layanan --}}
            <div class="space-y-3">
                @foreach ($transaksi->transaksiLayanan as $item)
                @php
                    $layananData = $this->formatLayananItem($item);
                @endphp

                <div class="flex items-start justify-between gap-3 pb-3 border-b border-base-300 last:border-0">
                    <div class="flex-1">
                        <h3 class="font-semibold text-base-content">{{ $item->nama_layanan }}</h3>
                        <p class="text-sm text-base-content/60">{{ $layananData['harga'] }}</p>

                        @if ($layananData['is_per_kg'] && !empty($layananData['jenis_pakaian']))
                        <div class="mt-2">
                            <p class="text-xs font-semibold text-base-content/70">Jenis Pakaian:</p>
                            <ul class="text-xs text-base-content/80 mt-1 space-y-0.5">
                                @foreach ($layananData['jenis_pakaian'] as $jp)
                                <li>• {{ $jp['nama_jenis'] ?? $jp['nama'] ?? 'N/A' }} ({{ $jp['jumlah'] ?? 0 }})</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>

                    <div class="text-right">
                        <p class="text-sm font-semibold text-base-content">{{ $layananData['quantity'] }}</p>
                        <p class="text-sm font-bold text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Info Kurir --}}
            @if ($transaksi->kurir_jemput_nama || $transaksi->kurir_antar_nama)
            <div class="space-y-2 pt-3 border-t border-base-300">
                @if ($transaksi->kurir_jemput_nama)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-base-content/60">Kurir Jemput</span>
                    <span class="font-semibold">{{ $transaksi->kurir_jemput_nama }}</span>
                </div>
                @endif
                @if ($transaksi->kurir_antar_nama)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-base-content/60">Kurir Antar</span>
                    <span class="font-semibold">{{ $transaksi->kurir_antar_nama }}</span>
                </div>
                @endif
            </div>
            @endif

            {{-- Catatan --}}
            @if ($transaksi->catatan)
            <div class="pt-3 border-t border-base-300">
                <p class="text-xs font-semibold text-base-content/70">Catatan:</p>
                <p class="text-sm text-base-content">{{ $transaksi->catatan }}</p>
            </div>
            @endif
        </x-card>

        {{-- Informasi Pembayaran --}}
        <x-card title="Pembayaran" subtitle="Detail pembayaran pesanan" class="shadow-lg border border-primary w-full"
            body-class="space-y-4">
            <x-slot:menu>
                {{-- Status Pembayaran --}}
                <x-badge :value="$transaksi->status_bayar" class="badge-sm {{ $this->getPaymentStatusBadgeClass() }} truncate" />
            </x-slot:menu>
            {{-- Rincian Biaya --}}
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-base-content/70">Subtotal</span>
                    <span class="text-sm font-semibold">Rp {{ number_format($transaksi->subtotal, 0, ',', '.')
                        }}</span>
                </div>

                @if ($transaksi->transaksiPromo->isNotEmpty())
                @foreach ($transaksi->transaksiPromo as $promo)
                <div class="flex justify-between items-center text-success">
                    <span class="text-sm">{{ $promo->nama_promo }}</span>
                    <span class="text-sm font-semibold">- Rp {{ number_format($promo->nilai_diskon_nominal, 0, ',',
                        '.') }}</span>
                </div>
                @endforeach
                @endif

                <div class="divider my-2"></div>

                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-base-content">Total</span>
                    <span class="text-lg font-bold text-primary">Rp {{ number_format($transaksi->total, 0, ',', '.')
                        }}</span>
                </div>
            </div>

            <div class="divider my-2"></div>

            {{-- Detail Pembayaran --}}
            <div class="space-y-2">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-base-content/60">Metode Pembayaran</span>
                    <span class="font-semibold">{{ $transaksi->metode_pembayaran }}</span>
                </div>

                @if ($transaksi->tipe_bayar)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-base-content/60">Tipe Pembayaran</span>
                    <span class="font-semibold">{{ $transaksi->tipe_bayar }}</span>
                </div>
                @endif

                @if ($transaksi->status_bayar === 'Sudah Bayar' && $transaksi->tanggal_bayar)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-base-content/60">Tanggal Bayar</span>
                    <span class="font-semibold">{{ $transaksi->tanggal_bayar->locale('id')->isoFormat('DD MMMM YYYY,
                        HH:mm') }}</span>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Foto Bukti Timbangan --}}
        @php
            $fotoTimbangan = $this->getFotoTimbangan();
        @endphp
        @if (!empty($fotoTimbangan))
        <x-card title="Foto Bukti Timbangan" subtitle="Bukti timbangan cucian Anda" class="shadow-lg border border-primary w-full">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($fotoTimbangan as $foto)
                @if (!empty($foto))
                <button type="button" wire:click="showImage('{{ $foto }}')" class="block overflow-hidden rounded-lg shadow-lg border-b-4 border-r-4 border-success active:border-0 active:shadow-sm transition-all cursor-pointer">
                    <img src="{{ $foto }}" alt="Foto Timbangan" class="w-full h-40 object-cover" />
                </button>
                @endif
                @endforeach
            </div>
        </x-card>
        @endif

        {{-- Foto Bukti Pembayaran --}}
        @php
            $fotoPembayaran = $this->getFotoPembayaran();
        @endphp
        @if (!empty($fotoPembayaran))
        <x-card title="Foto Bukti Pembayaran" subtitle="Bukti pembayaran Anda" class="shadow-lg border border-primary w-full">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($fotoPembayaran as $foto)
                @if (!empty($foto))
                <button type="button" wire:click="showImage('{{ $foto }}')" class="block overflow-hidden rounded-lg shadow-lg border-b-4 border-r-4 border-success active:border-0 active:shadow-sm transition-all cursor-pointer">
                    <img src="{{ $foto }}" alt="Foto Pembayaran" class="w-full h-40 object-cover" />
                </button>
                @endif
                @endforeach
            </div>
        </x-card>
        @endif
    </div>

    {{-- Modal untuk menampilkan gambar full --}}
    <x-modal wire:model="imageModal" title="Lihat Gambar" class="modal-bottom w-full backdrop-blur" persistent>
        <div class="flex flex-col items-center space-y-4">
            @if ($selectedImage)
            <img src="{{ $selectedImage }}" alt="Full Image" class="w-full max-h-[70vh] object-contain rounded-lg" />
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Tutup" class="btn-primary btn-block" @click="$wire.imageModal = false" />
        </x-slot:actions>
    </x-modal>
</div>

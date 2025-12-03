<div class="container mx-auto">
    <x-header title="Detail Layanan" subtitle="{{ $layanan->nama_layanan }} - {{ $layanan->kode_layanan }}" separator>
        <x-slot:actions>
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('pesanan.pelanggan') }}" responsive />
        </x-slot:actions>
    </x-header>

    <!-- Tabs Navigation -->
    <x-tabs wire:model="tabSelected" active-class="bg-{{ $this->getColorClass() }} rounded text-white!"
        label-class="font-semibold text-lg" label-div-class="bg-primary/10 rounded flex justify-around w-full p-2">
        <x-tab name="detail-tab" label="Detail" />
        <x-tab name="harga-tab" label="Harga" />
        <x-tab name="waktu-tab" label="Waktu" />
    </x-tabs>

    <!-- Detail Card -->
    <x-card wire:show="tabSelected === 'detail-tab'"
        class="shadow-lg border border-{{ $this->getColorClass() }} w-full mb-34" title="{{ $layanan->nama_layanan }}"
        subtitle="{{ $layanan->kode_layanan }}" body-class="space-y-4" shadow separator>
        <x-slot:menu>
            @if($layanan->is_popular)
            <x-badge value="Populer" class="badge-sm badge-{{ $this->getColorClass() }}" />
            @endif
            @if($layanan->icon)
            <x-icon name="{{ $layanan->icon }}" class="h-10 text-{{ $this->getColorClass() }}" />
            @else
            <x-icon name="o-sparkles" class="h-10 text-{{ $this->getColorClass() }}" />
            @endif
        </x-slot:menu>
        <div>
            <h3 class="font-semibold text-lg mb-2">Informasi Layanan</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-secondary">Kode Layanan:</span>
                    <span class="font-medium uppercase">{{ $layanan->kode_layanan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Nama Layanan:</span>
                    <span class="font-medium uppercase">{{ $layanan->nama_layanan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Tipe Layanan:</span>
                    <span class="font-medium uppercase">{{ ucfirst($layanan->tipe_layanan) }}</span>
                </div>
                @if($layanan->satuan)
                <div class="flex justify-between">
                    <span class="text-secondary">Satuan:</span>
                    <span class="font-medium uppercase">{{ $layanan->satuan }}</span>
                </div>
                @endif
            </div>
        </div>

        @if($layanan->deskripsi)
        <div>
            <h4 class="font-semibold mb-2">Deskripsi</h4>
            <p class="text-secondary">{{ $layanan->deskripsi }}</p>
        </div>
        @endif

        @if($layanan->include || $layanan->exclude)
        <div class="flex flex-row gap-8">
            @if($layanan->include && is_array($layanan->include) && count($layanan->include) > 0)
            <div class="flex-1">
                <h4 class="font-semibold mb-2 text-success">Cover Layanan:</h4>
                <ul class="flex flex-col gap-2 text-xs">
                    @foreach($layanan->include as $item)
                    <li>
                        <x-icon name="o-check-circle" class="size-4 me-2 inline-block text-success" />
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($layanan->exclude && is_array($layanan->exclude) && count($layanan->exclude) > 0)
            <div class="flex-1">
                <h4 class="font-semibold mb-2 text-error">Di Luar Cover:</h4>
                <ul class="flex flex-col gap-2 text-xs">
                    @foreach($layanan->exclude as $item)
                    <li>
                        <x-icon name="o-x-circle" class="size-4 me-2 inline-block text-error" />
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif
    </x-card>

    <!-- Harga Card -->
    <x-card wire:show="tabSelected === 'harga-tab'"
        class="shadow-lg border border-{{ $this->getColorClass() }} w-full mb-34" title="Rincian Harga"
        subtitle="Informasi harga dan ketentuan" body-class="space-y-4" shadow separator>
        <div class="space-y-4">
            <!-- Harga Utama -->
            <div>
                <h3 class="font-semibold text-lg mb-2">Informasi Harga</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-secondary">Tipe Harga:</span>
                        <span class="font-medium uppercase">{{ ucfirst($layanan->tipe_layanan) }}</span>
                    </div>

                    @if($layanan->tipe_layanan === 'per_kg' && $layanan->harga_per_kg > 0)
                    <div class="flex justify-between">
                        <span class="text-secondary">Harga per Kg:</span>
                        <span class="font-medium uppercase text-success">Rp {{ number_format($layanan->harga_per_kg, 0,
                            ',', '.') }}</span>
                    </div>
                    @endif

                    @if($layanan->tipe_layanan === 'per_satuan' && $layanan->harga_per_satuan > 0)
                    <div class="flex justify-between">
                        <span class="text-secondary">Harga per {{ $layanan->satuan }}:</span>
                        <span class="font-medium uppercase text-success">Rp {{ number_format($layanan->harga_per_satuan,
                            0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-secondary">Satuan:</span>
                        <span class="font-medium">{{ $layanan->satuan }}</span>
                    </div>
                </div>
            </div>

            <!-- Ketentuan Order -->
            @if($layanan->min_order || $layanan->max_order)
            <div>
                <h3 class="font-semibold text-lg mb-2">Ketentuan Order</h3>
                <div class="space-y-2">
                    @if($layanan->min_order)
                    <div class="flex justify-between">
                        <span class="text-secondary">Minimum Order:</span>
                        <span class="font-medium">{{ $layanan->min_order }} {{ $layanan->satuan }}</span>
                    </div>
                    @endif

                    @if($layanan->max_order)
                    <div class="flex justify-between">
                        <span class="text-secondary">Maksimum Order:</span>
                        <span class="font-medium">{{ $layanan->max_order }} {{ $layanan->satuan }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Contoh Perhitungan -->
            @if($layanan->tipe_layanan === 'per_kg' && $layanan->harga_per_kg > 0)
            <div>
                <h4 class="font-semibold mb-2">Contoh Perhitungan</h4>
                <div class="space-y-2">
                    <div class="text-sm text-secondary">
                        <span>Jika cucian Anda {{ $layanan->min_order ?? 5 }} kg</span>
                        <span class="block">({{ $layanan->min_order ?? 5 }} kg × Rp {{
                            number_format($layanan->harga_per_kg, 0, ',', '.') }}/kg)</span>
                    </div>
                    <div class="flex justify-between items-center bg-{{ $this->getColorClass() }}/10 rounded p-2">
                        <span class="text-sm">Total:</span>
                        <span class="font-medium text-{{ $this->getColorClass() }}">Rp {{
                            number_format(($layanan->min_order ?? 5) * $layanan->harga_per_kg, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            @if($layanan->tipe_layanan === 'per_satuan' && $layanan->harga_per_satuan > 0)
            <div>
                <h4 class="font-semibold mb-2">Contoh Perhitungan</h4>
                <div class="space-y-2">
                    <div class="text-sm text-secondary">
                        <span>Contoh 1: Jika pesanan {{ $layanan->min_order ?? 1 }} {{ $layanan->satuan }}</span>
                        <span class="block">({{ $layanan->min_order ?? 1 }} {{ $layanan->satuan }} × Rp {{
                            number_format($layanan->harga_per_satuan, 0, ',', '.') }}/{{ $layanan->satuan }})</span>
                    </div>
                    <div class="flex justify-between items-center bg-{{ $this->getColorClass() }}/10 rounded p-2">
                        <span class="text-sm">Total:</span>
                        <span class="font-medium text-{{ $this->getColorClass() }}">Rp {{
                            number_format(($layanan->min_order ?? 1) * $layanan->harga_per_satuan, 0, ',', '.')
                            }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-card>

    <!-- Waktu Card -->
    <x-card wire:show="tabSelected === 'waktu-tab'"
        class="shadow-lg border border-{{ $this->getColorClass() }} w-full mb-34" title="Estimasi Waktu"
        subtitle="Perkiraan waktu pengerjaan" body-class="space-y-4" shadow>
        <div class="space-y-4">
            <!-- Informasi Utama -->
            <div>
                <div class="text-center mb-4">
                    <div class="text-2xl font-bold text-{{ $this->getColorClass() }}">
                        {{ \App\Helper\Database\LayananHelper::formatEstimasiWaktu($layanan->durasi_jam) }}
                    </div>
                </div>
            </div>

            <!-- Timeline Estimasi -->
            <div>
                <h4 class="font-semibold mb-4">Proses Laundry</h4>
                <ul class="timeline timeline-vertical">
                    <li>
                        <div class="timeline-start timeline-box text-xs">Penjemputan</div>
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                    </li>
                    <li>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                        <div class="timeline-end timeline-box">Penimbangan</div>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                    </li>
                    <li>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                        <div class="timeline-start timeline-box">Pencucian</div>
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                    </li>
                    <li>
                       <hr class="bg-{{ $this->getColorClass() }}" />
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                        <div class="timeline-end timeline-box">Pengeringan</div>
                       <hr class="bg-{{ $this->getColorClass() }}" />
                    </li>
                    <li>
                       <hr class="bg-{{ $this->getColorClass() }}" />
                        <div class="timeline-start timeline-box">Pengemasan</div>
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                        <hr class="bg-{{ $this->getColorClass() }}" />
                    </li>
                    <li>
                       <hr class="bg-{{ $this->getColorClass() }}" />
                        <div class="timeline-end timeline-box">Pengiriman</div>
                        <div class="timeline-middle">
                            <x-icon name="iconpark.checkone" class="w-5 h-5 text-{{ $this->getColorClass() }}" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </x-card>

</div>

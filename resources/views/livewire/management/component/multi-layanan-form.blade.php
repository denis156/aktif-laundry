<div class="space-y-3">
    <label class="block text-sm font-semibold">
        <x-icon name="o-plus-circle" class="inline-block w-4 h-4 mr-1" />
        Layanan dalam Transaksi
    </label>

    @foreach($items as $index => $item)
    @php
    // Determine card background color based on layanan type
    $cardBgClass = 'bg-base-100';
    if (!empty($item['layanan_id'])) {
    $layanan = \App\Models\Layanan::find($item['layanan_id']);
    if ($layanan) {
    if ($layanan->tipe_layanan === 'per_kg') {
    $cardBgClass = 'bg-info/10';
    } elseif ($layanan->tipe_layanan === 'per_satuan') {
    $cardBgClass = 'bg-warning/10';
    }
    }
    }
    @endphp
    <div class="card {{ $cardBgClass }} border border-base-300 transition-colors duration-300">
        <div class="card-body p-4">
            <!-- Header Layanan -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    @php
                    $layananIcon = null;
                    $layananModel = null;
                    if (!empty($item['layanan_id'])) {
                    $layananModel = \App\Models\Layanan::find($item['layanan_id']);
                    if ($layananModel) {
                    $layananIcon = \App\Helper\Database\LayananHelper::getIcon($layananModel);
                    }
                    }
                    @endphp
                    @if($layananIcon)
                    <x-icon name="{{ $layananIcon }}" class="w-5 h-5 text-primary" />
                    @else
                    <x-icon name="o-sparkles" class="w-5 h-5 text-primary" />
                    @endif
                    <span class="font-bold">Layanan {{ $index + 1 }}</span>
                    @if($layananModel)
                    @if($layananModel->tipe_layanan === 'per_kg')
                    <span class="badge badge-info badge-sm">Per Kg</span>
                    @elseif($layananModel->tipe_layanan === 'per_satuan')
                    <span class="badge badge-warning badge-sm">Per Satuan</span>
                    @endif
                    @endif
                </div>
                @if(count($items) > 1)
                <button type="button" wire:click="removeLayanan({{ $index }})" class="btn btn-sm btn-error btn-circle">
                    <x-icon name="o-x-mark" class="w-4 h-4" />
                </button>
                @endif
            </div>

            <!-- Pilih Layanan & Jumlah dalam 1 Join -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Pilih Layanan & Jumlah</span>
                </label>

                @php
                $selectedLayanan = !empty($item['layanan_id']) ? collect($layananOptions)->firstWhere('id',
                $item['layanan_id']) : null;
                @endphp

                <div class="flex items-center gap-2">
                    {{-- Icon Layanan --}}
                    @if($selectedLayanan && !empty($selectedLayanan['icon']))
                    <x-icon name="{{ $selectedLayanan['icon'] }}" class="w-5 h-5 shrink-0" />
                    @else
                    <x-icon name="o-sparkles" class="w-5 h-5 shrink-0 opacity-30" />
                    @endif

                    <div class="join flex-1">
                        <select wire:model.live="items.{{ $index }}.layanan_id"
                            class="select select-bordered join-item flex-1">
                            <option value="">-- Pilih Layanan --</option>
                            @foreach($layananOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                            @endforeach
                        </select>

                        <!-- Dynamic input based on layanan type -->
                        @if(!empty($item['layanan_id']))
                        @php
                        $layanan = \App\Models\Layanan::find($item['layanan_id']);
                        @endphp

                        @if($layanan && $layanan->tipe_layanan == 'per_kg')
                        <input type="text" wire:model.live="items.{{ $index }}.berat_kg"
                            class="input input-bordered join-item w-24" placeholder="Berat" hint="kg" />
                        <div class="join-item bg-base-200 flex items-center px-3 border border-base-300">
                            <span class="text-sm font-medium">kg</span>
                        </div>
                        @elseif($layanan && $layanan->tipe_layanan == 'per_satuan')
                        <input type="number" wire:model.live="items.{{ $index }}.jumlah_satuan"
                            class="input input-bordered join-item w-20" placeholder="Jml" min="1" />
                        <div class="join-item bg-base-200 flex items-center px-3 border border-base-300">
                            <span class="text-sm font-medium">{{ $layanan->satuan ?? 'pcs' }}</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dynamic Form based on layanan type -->
            @if(!empty($item['layanan_id']))
            @php
            $layanan = \App\Models\Layanan::find($item['layanan_id']);
            @endphp

            @if($layanan && $layanan->tipe_layanan == 'per_kg')
            <!-- Form tambahan untuk layanan per kg (jenis pakaian) -->
            <div class="space-y-3 mt-3">
                <!-- Jenis Pakaian -->
                <div class="border border-base-300 rounded-lg p-3 bg-base-50">
                    <label class="block text-sm font-medium mb-2">Jenis Pakaian & Jumlah</label>
                    <div class="space-y-2">
                        @foreach($item['jenis_pakaian'] as $idx => $jp)
                        @php
                        $selectedJp = !empty($jp['jenis_id']) ? collect($jenisPakaianOptions)->firstWhere('id',
                        $jp['jenis_id']) : null;
                        @endphp
                        <div class="flex items-center gap-2">
                            @if($selectedJp && !empty($selectedJp['icon']))
                            <x-icon name="{{ $selectedJp['icon'] }}" class="w-5 h-5 shrink-0" />
                            @else
                            <x-icon name="o-square-3-stack-3d" class="w-5 h-5 shrink-0 opacity-30" />
                            @endif
                            <div class="join flex-1">
                                <select wire:model.live="items.{{ $index }}.jenis_pakaian.{{ $idx }}.jenis_id"
                                    class="select select-bordered join-item flex-1">
                                    <option value="">Pilih Jenis</option>
                                    @foreach($jenisPakaianOptions as $jpOption)
                                    <option value="{{ $jpOption['id'] }}">{{ $jpOption['name'] }}</option>
                                    @endforeach
                                </select>
                                <input type="number"
                                    wire:model.live="items.{{ $index }}.jenis_pakaian.{{ $idx }}.jumlah"
                                    class="input input-bordered join-item w-20" placeholder="Qty" min="1" />
                                @if(count($item['jenis_pakaian']) > 1)
                                <button type="button" wire:click="removeJenisPakaian({{ $index }}, {{ $idx }})"
                                    class="btn btn-error join-item">
                                    <x-icon name="o-trash" class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addJenisPakaian({{ $index }})"
                        class="btn btn-outline btn-sm btn-primary w-full mt-2">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        Tambah Jenis Pakaian
                    </button>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
    @endforeach

    <!-- Tombol Tambah Layanan -->
    <button type="button" wire:click="addLayanan" class="btn btn-outline btn-primary w-full">
        <x-icon name="o-plus" class="w-4 h-4" />
        Tambah Layanan Lain
    </button>

</div>

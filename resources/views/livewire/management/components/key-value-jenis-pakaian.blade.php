<div class="space-y-3">
    <label class="block text-sm font-semibold">
        <x-icon name="o-tag" class="inline-block w-4 h-4 mr-1" />
        Jenis Pakaian & Jumlah
    </label>

    @foreach($items as $index => $item)
    @php
    $selectedOption = !empty($item['jenis_id']) ? $jenisPakaianOptions->firstWhere('id', $item['jenis_id']) : null;
    @endphp
    <div class="flex items-center gap-2">
        {{-- Icon --}}
        @if($selectedOption && !empty($selectedOption['icon']))
        <x-icon name="{{ $selectedOption['icon'] }}" class="w-5 h-5 shrink-0" />
        @else
        <x-icon name="o-square-3-stack-3d" class="w-5 h-5 shrink-0 opacity-30" />
        @endif

        <div class="join flex-1">
            {{-- Select Jenis Pakaian --}}
            <select wire:model.live="items.{{ $index }}.jenis_id" class="select select-bordered join-item flex-1">
                <option value="">Pilih Jenis Pakaian</option>
                @foreach($jenisPakaianOptions as $option)
                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                @endforeach
            </select>

            {{-- Input Jumlah --}}
            <input type="number" wire:model.live="items.{{ $index }}.jumlah" class="input input-bordered join-item w-24"
                placeholder="Qty" min="1" />

            {{-- Button Remove --}}
            <button type="button" wire:click="removeRow({{ $index }})" class="btn btn-error join-item"
                @if(count($items)===1) disabled @endif>
                <x-icon name="o-trash" class="w-4 h-4" />
            </button>
        </div>
    </div>
    @endforeach

    {{-- Button Add New Row --}}
    <button type="button" wire:click="addRow" class="btn btn-outline btn-sm btn-primary w-full">
        <x-icon name="o-plus" class="w-4 h-4" />
        Tambah Jenis Pakaian
    </button>

    <p class="text-xs text-gray-500">Pilih jenis pakaian dan masukkan jumlahnya</p>

    {{-- Hidden input untuk form submission --}}
    <input type="hidden" wire:model="outputString" name="jenis_pakaian_detail" />
</div>

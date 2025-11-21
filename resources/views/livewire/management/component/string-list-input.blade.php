<div class="space-y-2">
    @foreach($items as $index => $item)
        <div wire:key="string-item-{{ $index }}">
            <x-input
                wire:model.live.debounce.300ms="items.{{ $index }}.value"
                placeholder="{{ $placeholder }}"
            >
                @if(count($items) > 1)
                    <x-slot:append>
                        {{-- Add `join-item` to make it visually connected --}}
                        <button
                            type="button"
                            wire:click="removeRow({{ $index }})"
                            class="join-item btn btn-error">
                            <x-icon name="o-trash" class="w-4 h-4" />
                        </button>
                    </x-slot:append>
                @endif
            </x-input>
        </div>
    @endforeach

    <button
        type="button"
        wire:click="addRow"
        class="btn btn-outline btn-sm btn-primary w-full">
        <x-icon name="o-plus" class="w-4 h-4" />
        Tambah Item
    </button>
</div>

<div>
    <x-choices label="{{ $label }}" wire:model.live="selectedIcon" :options="$iconOptions"
        placeholder="{{ $placeholder }}" hint="{{ $hint }}" min-chars="2" debounce="300ms" single searchable clearable>
        {{-- Item slot with icon preview --}}
        @scope('item', $icon)
        <x-list-item :item="$icon">
            <x-slot:avatar>
                <x-icon name="{{ $icon['id'] }}" class="w-6 h-6" />
            </x-slot:avatar>
            <x-slot:actions>
                <span class="text-xs text-base-content/50">{{ $icon['id'] }}</span>
            </x-slot:actions>
        </x-list-item>
        @endscope

        {{-- Selection slot with icon preview --}}
        @scope('selection', $icon)
        <div class="flex items-center gap-2">
            <x-icon name="{{ $icon['id'] }}" class="w-5 h-5" />
            <span>{{ $icon['name'] }}</span>
        </div>
        @endscope
    </x-choices>
</div>

<nav class="dock bg-primary text-primary-content rounded-t-2xl bottom-0 z-50">
    @foreach ($navigationItems as $item)
        @php
            $buttonClasses = $this->getNavButtonClasses($item['route']);
            $contentClasses = $this->getNavContentClasses($item['route']);
        @endphp

        <button {{ $buttonClasses ? "class='{$buttonClasses}'" : '' }} wire:navigate.hover href="{{ route($item['route']) }}">
            <x-icon name="{{ $item['icon'] }}" class="{{ $item['icon_size'] }} {{ $contentClasses['icon'] }}" />
            <span class="dock-label {{ $contentClasses['label'] }}">{{ $item['name'] }}</span>
        </button>
    @endforeach
</nav>

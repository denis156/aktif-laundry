<nav class="dock bg-base-200 text-base-content border-t-2 border-primary rounded-t-2xl bottom-0 z-50">
    @foreach ($navigationItems as $item)
        @php
            $buttonClasses = $this->getNavButtonClasses($item['route']);
            $contentClasses = $this->getNavContentClasses($item['route']);
        @endphp

        <a {{ $buttonClasses ? "class='{$buttonClasses}'" : '' }} href="{{ route($item['route']) }}" wire:navigate>
            <x-icon name="{{ $item['icon'] }}" class="{{ $item['icon_size'] }} {{ $contentClasses['icon'] }}" />
            <span class="dock-label {{ $contentClasses['label'] }}">{{ $item['name'] }}</span>
        </a>
    @endforeach
</nav>

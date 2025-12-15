<nav class="navbar bg-primary text-primary-content sticky top-0 z-50" wire:poll.30s>
    <div class="navbar-start">
        <x-button class="indicator btn-circle" icon="iconpark.message-o" link="{{ route('chat.kurir') }}">
            @if($this->unreadMessagesCount > 0)
                <x-badge value="{{ $this->unreadMessagesCount }}" class="badge-success badge-xs indicator-item" />
            @endif
        </x-button>
    </div>
    <div class="navbar-center">
        <span class="text-md font-extrabold uppercase">kurir {{ config('app.name') }}</span>
    </div>
    <div class="navbar-end">
        <a href="{{ route('profile.kurir') }}" wire:navigate class="cursor-pointer">
            <x-avatar :image="$this->avatarUrl"
                class="w-10 h-10 rounded-full ring-2 ring-success ring-offset-2 ring-offset-base-200 hover:ring-primary transition-all" />
        </a>
    </div>
</nav>

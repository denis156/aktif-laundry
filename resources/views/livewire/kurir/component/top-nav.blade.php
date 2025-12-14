<nav class="navbar bg-primary text-primary-content sticky top-0 z-50">
    <div class="navbar-start">
        <x-button icon="iconpark.message-o" class="btn-circle" link="{{ route('chat.kurir') }}" />
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

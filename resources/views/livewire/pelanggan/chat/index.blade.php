<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Chat" subtitle="Chat dengan Admin & Kurir" icon="iconpark.message-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:middle class="justify-end!">
            <x-input placeholder="Cari chat..." wire:model.live.debounce="search" clearable icon="iconpark.search-o"
            class="input-primary" />
        </x-slot:middle>
    </x-header>
    <div class="space-y-4 mb-24">
        <!-- Conversations List -->
        @forelse($this->conversations as $conversation)
        @php
        $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder(
        $conversation->participant_avatar,
        $conversation->participant_name
        );
        @endphp

        <a href="{{ route('chat-room.pelanggan', $conversation->id) }}" wire:navigate class="block">
            <x-card wire:key="conversation-{{ $conversation->id }}" title="{{ $conversation->participant_name }}"
                subtitle="{{ $conversation->last_message_at->diffForHumans() }}"
                class="shadow-lg border border-b-4 border-r-4 border-primary active:border-0 active:shadow-sm transition-all cursor-pointer">
                <x-slot:menu>
                    <div class="avatar {{ $conversation->is_online ? 'online' : 'offline' }}">
                        <div class="w-10 rounded-full">
                            <img src="{{ $avatarUrl }}" alt="{{ $conversation->participant_name }}" />
                        </div>
                    </div>
                </x-slot:menu>

                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm truncate {{ $conversation->unread_count > 0 ? 'text-success font-semibold' : 'text-base-content/60' }}">
                                {{ Str::words($conversation->last_message, 4, '...') }}
                            </span>
                            @if($conversation->unread_count > 0)
                            <span class="text-xs text-success">•</span>
                            <span class="text-xs text-success font-bold">
                                {{ $conversation->unread_count }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 items-end shrink-0">
                        <x-badge value="{{ $conversation->participant_type }}" class="badge-xs badge-ghost" />
                    </div>
                </div>
            </x-card>
        </a>
        @empty
        <x-card class="shadow-lg border border-primary"
            body-class="flex flex-col items-center justify-center py-12 space-y-4">
            <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                <x-icon name="iconpark.message-o" class="h-10 text-base-content/40" />
            </div>
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-base-content">Belum Ada Chat</h3>
                <p class="text-sm text-base-content/60 max-w-md">
                    Chat dengan admin dan kurir akan muncul di sini
                </p>
            </div>
        </x-card>
        @endforelse
    </div>
</div>

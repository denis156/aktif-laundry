<div class="container mx-auto" wire:poll.visible.10s>
    <x-header title="Chat" subtitle="Chat dengan Admin & Pelanggan" icon="iconpark.message-o"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8" separator>
        <x-slot:middle class="justify-end!">
            <x-input placeholder="Cari chat..." wire:model.live.debounce="search" clearable icon="iconpark.search-o"
            class="input-primary" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="iconpark.plus-o" label="Buat Chat" @click="$wire.createModal = true" class="btn-primary" responsive />
        </x-slot:actions>
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

        <a href="{{ route('chat-room.kurir', $conversation->id) }}" wire:navigate class="block">
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
                    Chat dengan admin dan pelanggan akan muncul di sini
                </p>
            </div>
        </x-card>
        @endforelse
    </div>

    <!-- CREATE CONVERSATION MODAL -->
    <x-modal wire:model="createModal" title="Buat Chat Baru" subtitle="Pilih dengan siapa Anda ingin memulai chat"
        class="modal-bottom w-full backdrop-blur space-y-4" persistent>
        <div class="space-y-4">
            @php
                $participantTypeOptions = [
                    ['id' => 'User', 'name' => 'Admin'],
                    ['id' => 'Pelanggan', 'name' => 'Pelanggan'],
                ];
            @endphp

            <x-choices label="Tipe Participant" wire:model.live="participantTypes" icon="iconpark.peoples-o"
                :options="$participantTypeOptions" option-value="id" option-label="name" />

            <x-choices-offline label="Pilih Participant" wire:model="participantId" icon="iconpark.user-o"
                :options="$this->participants" option-value="id" option-label="name" placeholder="Cari participant..."
                single searchable clearable :disabled="empty($participantTypes)"
                hint="{{ empty($participantTypes) ? 'Pilih tipe participant terlebih dahulu' : '' }}"
                height="max-h-44">

                {{-- Item slot --}}
                @scope('item', $participant)
                    <x-list-item :item="$participant">
                        <x-slot:avatar>
                            @php
                                $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder(
                                    $participant['avatar'] ?? null,
                                    $participant['name']
                                );
                            @endphp
                            <div class="avatar">
                                <div class="w-11 rounded-full">
                                    <img src="{{ $avatarUrl }}" alt="{{ $participant['name'] }}" />
                                </div>
                            </div>
                        </x-slot:avatar>

                        <x-slot:value>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">{{ $participant['name'] }}</span>
                                <x-badge :value="$participant['type']" class="badge-sm badge-ghost" />
                            </div>
                        </x-slot:value>

                        <x-slot:sub-value>
                            <span class="text-xs text-base-content/60 truncate max-w-md">
                                {{ $participant['address'] }}
                            </span>
                        </x-slot:sub-value>
                    </x-list-item>
                @endscope

                {{-- Selection slot --}}
                @scope('selection', $participant)
                    {{ $participant['name'] }} ({{ $participant['type'] }})
                @endscope
            </x-choices-offline>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" @click="$wire.createModal = false" class="btn-ghost" />
                <x-button label="Buat Chat" wire:click="createConversation" spinner class="btn-primary" />
            </div>
        </x-slot:actions>
    </x-modal>
</div>

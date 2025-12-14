<div>
    <!-- HEADER -->
    <x-header title="Chat" icon="o-chat-bubble-left-right"
        icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        subtitle="Percakapan dengan Pelanggan, Kurir & Staf" separator progress-indicator>
        <x-slot:middle class="justify-end">
            <x-input placeholder="Cari percakapan..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Buat Chat" @click="$wire.createModal = true" responsive icon="o-plus"
                class="btn-success" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- CONTENT -->
    <x-card class="shadow-sm" body-class="border-t-2 border-accent border-dashed p-2 h-[60dvh] overflow-y-auto no-scrollbar" title="Daftar Chat"
        subtitle="Kelola komunikasi dengan pelanggan, kurir, dan staf">
        @forelse($this->conversations as $conversation)
            @php
                $avatarUrl = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder(
                    $conversation->participant_avatar,
                    $conversation->participant_name
                );
            @endphp

            <x-list-item :item="$conversation" value="participant_name">
                <x-slot:avatar>
                    <div class="avatar {{ $conversation->is_online ? 'online' : 'offline' }}">
                        <div class="w-11 rounded-full">
                            <img src="{{ $avatarUrl }}" alt="{{ $conversation->participant_name }}" />
                        </div>
                    </div>
                </x-slot:avatar>

                <x-slot:value>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ $conversation->participant_name }}</span>
                        <x-badge value="{{ $conversation->participant_type }}" class="badge-sm badge-ghost" />
                    </div>
                </x-slot:value>

                <x-slot:sub-value>
                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                        <span class="truncate max-w-md">{{ $conversation->last_message }}</span>
                        <span class="text-xs">• {{ $conversation->last_message_at->diffForHumans() }}</span>
                    </div>
                </x-slot:sub-value>

                <x-slot:actions>
                    <div class="flex items-center gap-2">
                        @if($conversation->unread_count > 0)
                            <x-badge value="{{ $conversation->unread_count }}" class="badge-primary" />
                        @endif
                        <x-button icon="o-chat-bubble-left-right" class="btn-sm btn-outline btn-info"
                            link="{{ route('chat.room', $conversation->id) }}" wire:navigate
                            label="Chat" responsive />
                        <x-button icon="o-trash" class="btn-sm btn-outline btn-error"
                            wire:click="confirmDelete({{ $conversation->id }})"
                            label="Hapus" responsive />
                    </div>
                </x-slot:actions>
            </x-list-item>
        @empty
            <div class="flex h-full flex-col items-center justify-center gap-2 p-4">
                <x-icon name="o-chat-bubble-left-right" class="w-12 h-12 text-base-content/40" />
                <p class="text-sm text-base-content/60">Belum ada chat.</p>
            </div>
        @endforelse
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Chat" subtitle="Saring data sesuai kebutuhan" right separator
        with-close-button class="lg:w-1/3">
        <div class="space-y-5">
            @php
                $typeOptions = [
                    ['id' => '', 'name' => 'Semua Tipe'],
                    ['id' => 'Pelanggan', 'name' => 'Pelanggan'],
                    ['id' => 'Kurir', 'name' => 'Kurir'],
                    ['id' => 'User', 'name' => 'Staf'],
                ];
            @endphp

            <x-select label="Tipe Participant" wire:model.live="filterType" icon="o-user-group"
                :options="$typeOptions" option-value="id" option-label="name" />
        </div>

        <x-slot:actions>
            <x-button label="Reset Filter" icon="o-x-mark" wire:click="clear" spinner class="btn-outline btn-error" />
            <x-button label="Terapkan" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>

    <!-- CREATE CONVERSATION MODAL -->
    <x-modal wire:model="createModal" title="Buat Chat Baru" subtitle="Pilih dengan siapa Anda ingin memulai chat"
        box-class="w-full sm:max-w-lg space-y-4" class="modal-bottom sm:modal-middle backdrop-blur-md">
        <div class="space-y-4">
            @php
                $participantTypeOptions = [
                    ['id' => 'User', 'name' => 'Staf'],
                    ['id' => 'Kurir', 'name' => 'Kurir'],
                    ['id' => 'Pelanggan', 'name' => 'Pelanggan'],
                ];
            @endphp

            <x-choices label="Tipe Participant" wire:model.live="participantTypes" icon="o-user-group"
                :options="$participantTypeOptions" option-value="id" option-label="name" />

            <x-choices-offline label="Pilih Participant" wire:model="participantId" icon="o-user"
                :options="$this->participants" option-value="id" option-label="name" placeholder="Cari participant..."
                single searchable clearable :disabled="empty($participantTypes)"
                hint="{{ empty($participantTypes) ? 'Pilih tipe participant terlebih dahulu' : '' }}">

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
            <x-button label="Batal" @click="$wire.createModal = false" class="btn-ghost" />
            <x-button label="Buat Percakapan" wire:click="createConversation" spinner class="btn-success"
                icon="o-chat-bubble-left-right" />
        </x-slot:actions>
    </x-modal>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" box-class="max-w-md" class="modal-bottom sm:modal-middle backdrop-blur-md">
        <div class="text-center space-y-2">
            <div class="flex justify-center">
                <x-icon name="o-exclamation-triangle" class="w-18 h-18 p-4 bg-error rounded-full text-error-content" />
            </div>

            <div>
                <h3 class="text-lg font-bold text-error">Konfirmasi Hapus!</h3>
                <p class="text-sm text-base-content mt-2">Data yang sudah dihapus tidak dapat dikembalikan.</p>
                <p class="text-sm text-base-content mt-2">Apakah Anda yakin ingin menghapus percakapan dengan <span
                        class="font-bold">{{ $conversationToDeleteName }}</span> ?</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Hapus" wire:click="deleteConversation" spinner class="btn-error btn-block" icon="o-trash" />
        </x-slot:actions>
    </x-modal>
</div>

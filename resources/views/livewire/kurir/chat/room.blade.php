<div class="container mx-auto" wire:ignore.self>
    @php
    $otherParticipant = $this->otherParticipant;
    $participantName = $otherParticipant?->nama ?? $otherParticipant?->name ?? 'Unknown';
    $participantAvatar = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder(
    $otherParticipant?->avatar_url ?? null,
    $participantName
    );
    $lastMessageTime = $this->conversation?->last_message_at?->diffForHumans() ?? 'Baru saja';
    @endphp

    <!-- HEADER -->
    <x-header title="{{ $participantName }}" subtitle="Chat terakhir • {{ $lastMessageTime }}"
        icon="iconpark.message-o" icon-classes="bg-primary text-primary-content rounded-full p-1 w-8 h-8"
        separator progress-indicator="sendMessage,sendFileMessage">
        <x-slot:actions>
            <x-button icon="iconpark.delete-o" wire:click="confirmDelete" class="btn-error btn-sm" label="Hapus" responsive />
            <x-button icon="iconpark.lefttwo-o" class="btn-secondary btn-sm" label="Kembali"
                link="{{ route('chat.kurir') }}" responsive />
        </x-slot:actions>
    </x-header>

    <!-- CHAT MESSAGES -->
    <x-card class="shadow-lg border border-primary w-full" body-class="h-[60dvh] overflow-y-auto no-scrollbar" x-data="{
            isNearBottom: true,
            scrollToBottom() {
                const container = this.$el.querySelector('.overflow-y-auto');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },
            checkScrollPosition() {
                const container = this.$el.querySelector('.overflow-y-auto');
                if (container) {
                    const threshold = 100;
                    this.isNearBottom = (container.scrollHeight - container.scrollTop - container.clientHeight) < threshold;
                }
            }
        }" x-init="
            $nextTick(() => scrollToBottom());
            const container = this.$el.querySelector('.overflow-y-auto');
            if (container) {
                container.addEventListener('scroll', () => checkScrollPosition());
            }
            Livewire.hook('morph.updated', () => {
                if (this.isNearBottom) {
                    $nextTick(() => scrollToBottom());
                }
            });
        " @message-sent.window="$nextTick(() => scrollToBottom())"
        @new-message-received.window="$nextTick(() => { isNearBottom = true; scrollToBottom(); })" wire:key="chat-card">

        <!-- Messages -->
        <div class="{{ $this->messages->isEmpty() ? 'h-full flex items-center justify-center' : 'space-y-4' }}"
            id="messages-container" wire:poll.visible>
            @forelse($this->messages as $msg)
            @php
            $isMyMessage = $msg->sender_type === \App\Models\Kurir::class && $msg->sender_id ===
            Auth::guard('kurir')->id();
            $sender = $msg->sender;
            $senderName = $sender?->nama ?? $sender?->name ?? 'Unknown';
            $senderAvatar = \App\Helper\AvatarPlaceholder::getAvatarOrPlaceholder(
            $sender?->avatar_url ?? null,
            $senderName
            );
            @endphp

            <div class="chat {{ $isMyMessage ? 'chat-end' : 'chat-start' }}">
                <div class="chat-image avatar">
                    <div class="w-8 rounded-full">
                        <img src="{{ $senderAvatar }}" alt="{{ $senderName }}" />
                    </div>
                </div>
                <div class="chat-header text-xs">
                    {{ $senderName }}
                    <time class="text-xs opacity-50">{{ $msg->created_at->format('H:i') }}</time>
                </div>
                <div class="chat-bubble {{ $isMyMessage ? 'chat-bubble-primary' : 'chat-bubble-secondary' }} text-sm">
                    @if($msg->message && $msg->message !== '-' && (!$msg->hasAttachment() || $msg->message !==
                    $msg->file_name))
                    <div class="{{ $msg->hasAttachment() ? 'mb-2' : '' }}">
                        {{ $msg->message }}
                    </div>
                    @endif

                    @if($msg->hasAttachment())
                    @php
                    $isImage = str_starts_with($msg->file_type ?? '', 'image/');
                    @endphp

                    <div>
                        @if($isImage)
                        <button type="button" wire:click="viewImage('{{ $msg->file_path }}', '{{ $msg->file_name }}')"
                            class="block cursor-pointer w-full">
                            <img src="{{ Storage::url($msg->file_path) }}" alt="{{ $msg->file_name }}"
                                class="w-full max-w-xs rounded-lg" />
                        </button>
                        <p class="text-xs opacity-75 mt-1">{{ $msg->file_name }}
                            ({{ $msg->getFileSizeFormatted() }})</p>
                        @else
                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank"
                            class="flex items-center gap-2 text-xs underline">
                            <x-icon name="iconpark.paperclip-o" class="w-4 h-4" />
                            {{ $msg->file_name }} ({{ $msg->getFileSizeFormatted() }})
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="chat-footer opacity-50 text-xs">
                    {{ $msg->isRead() ? 'Dibaca' : 'Terkirim' }}
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center gap-2">
                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center">
                    <x-icon name="iconpark.message-o" class="h-8 text-base-content/40" />
                </div>
                <p class="text-sm text-base-content/60">Belum ada pesan. Mulai percakapan!</p>
            </div>
            @endforelse
        </div>

        <x-slot:actions separator>
            {{-- Hidden File Input --}}
            <input type="file" wire:model="fileUpload" id="file-upload" class="hidden"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" />

            <form wire:submit="sendMessage" class="w-full">
                <x-input wire:model="message" placeholder="Tulis pesanmu disini..." class="input-primary">
                    {{-- File Upload Button --}}
                    <x-slot:prepend>
                        <x-button icon="iconpark.paperclip-o" class="join-item btn-secondary" type="button"
                            onclick="document.getElementById('file-upload').click()" />
                    </x-slot:prepend>

                    {{-- Send Message Button --}}
                    <x-slot:append>
                        <x-button label="Kirim" icon="iconpark.send-o" type="submit" class="join-item btn-primary"
                            spinner />
                    </x-slot:append>
                </x-input>

                @error('message') <span class="text-error text-xs block mt-1">{{ $message }}</span> @enderror
                @error('file') <span class="text-error text-xs block mt-1">{{ $message }}</span> @enderror
            </form>
        </x-slot:actions>
    </x-card>

    <!-- FILE PREVIEW MODAL -->
    <x-modal wire:model="filePreviewModal" class="modal-bottom w-full backdrop-blur" persistent>
        @if($fileUpload)
        <div class="space-y-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <x-icon name="iconpark.paperclip-o" class="w-5 h-5" />
                Preview File
            </h3>

            {{-- File Preview --}}
            <div class="rounded-lg flex items-center justify-center min-h-48">
                @if(str_starts_with($fileUpload->getMimeType(), 'image/'))
                <img src="{{ $fileUpload->temporaryUrl() }}" alt="Preview" class="max-w-full max-h-96 rounded-lg" />
                @else
                <div class="text-center space-y-2">
                    <x-icon name="iconpark.file-o" class="w-16 h-16 mx-auto text-base-content/40" />
                    <p class="text-sm font-medium">{{ $fileUpload->getClientOriginalName() }}</p>
                    <p class="text-xs text-base-content/60">{{ number_format($fileUpload->getSize() / 1024, 2) }}
                        KB</p>
                </div>
                @endif
            </div>

            {{-- Message Input --}}
            <x-input wire:model="fileMessage" label="Pesan (Opsional)" placeholder="Tulis pesan untuk file ini..." />

            @error('fileMessage') <span class="text-error text-xs">{{ $message }}</span> @enderror
            @error('fileUpload') <span class="text-error text-xs">{{ $message }}</span> @enderror
        </div>

        <x-slot:actions>
            <div class="flex gap-2 w-full">
                <x-button label="Batal" wire:click="cancelFileUpload" class="btn-ghost flex-1" />
                <x-button label="Kirim" wire:click="sendFileMessage" spinner class="btn-primary flex-1" />
            </div>
        </x-slot:actions>
        @endif
    </x-modal>

    <!-- IMAGE VIEWER MODAL -->
    <x-modal wire:model="imageViewerModal" class="modal-bottom w-full backdrop-blur" persistent>
        @if($selectedImageUrl)
        <div class="space-y-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <x-icon name="iconpark.pic-o" class="w-5 h-5" />
                {{ $selectedImageName }}
            </h3>

            {{-- Full Size Image --}}
            <div class="rounded-lg flex items-center justify-center max-h-[60vh] overflow-auto">
                <img src="{{ Storage::url($selectedImageUrl) }}" alt="{{ $selectedImageName }}"
                    class="w-full h-auto rounded-lg" />
            </div>
        </div>

        <x-slot:actions>
            <div class="flex gap-2 w-full">
                <x-button label="Tutup" wire:click="closeImageViewer" class="btn-ghost flex-1" />
                <a href="{{ Storage::url($selectedImageUrl) }}" download="{{ $selectedImageName }}"
                    class="btn btn-primary flex-1">
                    Download
                </a>
            </div>
        </x-slot:actions>
        @endif
    </x-modal>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" title="Konfirmasi Hapus Chat" class="modal-bottom w-full backdrop-blur" persistent>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-icon name="iconpark.delete-o" class="w-16 h-16 text-error" />
            </div>
            <p class="text-center text-base">Apakah Anda yakin ingin menghapus percakapan ini?</p>
            <p class="text-center text-sm text-base-content/60">Percakapan dengan <span class="font-bold">{{ $participantName }}</span> akan dihapus secara permanen</p>
        </div>

        <x-slot:actions>
            <div class="grid grid-cols-2 gap-4 w-full">
                <x-button label="Batal" class="btn-ghost" @click="$wire.deleteModal = false" />
                <x-button label="Ya, Hapus" class="btn-error" wire:click="deleteConversation" spinner />
            </div>
        </x-slot:actions>
    </x-modal>
</div>

<?php

declare(strict_types=1);

namespace App\Livewire\Management\Chat;

use App\Helper\Database\ChatHelper;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Room Chat')]
#[Layout('layouts.management.app')]
class Room extends Component
{
    use WithFileUploads;

    public int $conversationId;

    public string $message = '';

    public $file;

    public $fileUpload;

    public string $fileMessage = '';

    public bool $deleteModal = false;

    public bool $filePreviewModal = false;

    public bool $imageViewerModal = false;

    public ?string $selectedImageUrl = null;

    public ?string $selectedImageName = null;

    public function mount(int $conversation): void
    {
        $this->conversationId = $conversation;

        // Mark messages as read
        $conversationModel = Conversation::find($conversation);
        if ($conversationModel) {
            ChatHelper::markAllMessagesAsRead(
                $conversationModel,
                User::class,
                Auth::id()
            );
        }
    }

    public function updatedFileUpload(): void
    {
        // When file is selected, show preview modal
        if ($this->fileUpload) {
            $this->filePreviewModal = true;
        }
    }

    public function getConversationProperty()
    {
        return Conversation::with(['participantOne', 'participantTwo'])
            ->find($this->conversationId);
    }

    public function getOtherParticipantProperty()
    {
        if (! $this->conversation) {
            return null;
        }

        return ChatHelper::getParticipantModel(
            $this->conversation->participant_one_type === User::class && $this->conversation->participant_one_id === Auth::id()
                ? $this->conversation->participant_two_type
                : $this->conversation->participant_one_type,
            $this->conversation->participant_one_type === User::class && $this->conversation->participant_one_id === Auth::id()
                ? $this->conversation->participant_two_id
                : $this->conversation->participant_one_id
        );
    }

    public function getMessagesProperty()
    {
        if (! $this->conversation) {
            return collect();
        }

        return ChatHelper::getMessages($this->conversation, 50);
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|string|max:5000',
        ], [
            'message.required' => 'Pesan harus diisi.',
        ]);

        if (! $this->conversation) {
            return;
        }

        // Create message without file
        $this->conversation->messages()->create([
            'sender_type' => User::class,
            'sender_id' => Auth::id(),
            'message' => $this->message,
            'file_path' => null,
            'file_name' => null,
            'file_type' => null,
            'file_size' => null,
        ]);

        // Update last_message_at
        $this->conversation->update([
            'last_message_at' => now(),
        ]);

        $this->reset(['message']);
    }

    public function sendFileMessage(): void
    {
        $this->validate([
            'fileMessage' => 'nullable|string|max:5000',
            'fileUpload' => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ], [
            'fileUpload.required' => 'File harus dipilih.',
            'fileUpload.max' => 'Ukuran file maksimal 5MB.',
            'fileUpload.mimes' => 'Format file harus jpg, jpeg, png, gif, pdf, doc, atau docx.',
        ]);

        if (! $this->conversation) {
            return;
        }

        // Store file
        $path = $this->fileUpload->store('chat-attachments', 'public');
        $fileName = $this->fileUpload->getClientOriginalName();

        // Create message with file
        $this->conversation->messages()->create([
            'sender_type' => User::class,
            'sender_id' => Auth::id(),
            'message' => $this->fileMessage ?: $fileName,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_type' => $this->fileUpload->getMimeType(),
            'file_size' => $this->fileUpload->getSize(),
        ]);

        // Update last_message_at
        $this->conversation->update([
            'last_message_at' => now(),
        ]);

        $this->reset(['fileMessage', 'fileUpload']);
        $this->filePreviewModal = false;
    }

    public function cancelFileUpload(): void
    {
        $this->reset(['fileMessage', 'fileUpload']);
        $this->filePreviewModal = false;
    }

    public function viewImage(string $filePath, string $fileName): void
    {
        $this->selectedImageUrl = $filePath;
        $this->selectedImageName = $fileName;
        $this->imageViewerModal = true;
    }

    public function closeImageViewer(): void
    {
        $this->reset(['selectedImageUrl', 'selectedImageName']);
        $this->imageViewerModal = false;
    }

    public function confirmDelete(): void
    {
        $this->deleteModal = true;
    }

    public function deleteConversation()
    {
        if (! $this->conversation) {
            return;
        }

        // Hapus conversation (observer akan handle file deletion dan messages)
        $this->conversation->delete();

        return $this->redirect(route('chat.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.management.chat.room');
    }
}

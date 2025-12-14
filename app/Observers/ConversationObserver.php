<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Storage;

class ConversationObserver
{
    /**
     * Handle the Conversation "deleting" event.
     * This runs before the conversation is deleted, so we can still access messages.
     */
    public function deleting(Conversation $conversation): void
    {
        // Get all messages with attachments
        $messagesWithFiles = $conversation->messages()
            ->whereNotNull('file_path')
            ->get();

        // Delete all file attachments from storage
        foreach ($messagesWithFiles as $message) {
            if ($message->file_path && Storage::disk('public')->exists($message->file_path)) {
                Storage::disk('public')->delete($message->file_path);
            }
        }

        // Delete all messages (this will be done by cascade, but we explicitly do it here)
        $conversation->messages()->delete();
    }
}

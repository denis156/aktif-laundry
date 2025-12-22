<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helper\FirebaseHelper;
use App\Models\ChatMessage;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChatMessageObserver
{
    /**
     * Handle the ChatMessage "created" event.
     * Kirim notifikasi ke receiver saat ada pesan baru
     */
    public function created(ChatMessage $chatMessage): void
    {
        try {
            // Load relationship yang diperlukan
            $chatMessage->load(['conversation', 'sender']);

            $conversation = $chatMessage->conversation;

            if (! $conversation) {
                Log::warning('ChatMessageObserver: Conversation not found', [
                    'message_id' => $chatMessage->id,
                ]);

                return;
            }

            // Tentukan siapa receiver (bukan sender) dengan manual load
            $receiver = null;
            $receiverRole = null;
            $receiverType = null;
            $receiverId = null;

            // Check participant one - jika adalah sender, maka participant two adalah receiver
            if ($conversation->participant_one_type === $chatMessage->sender_type &&
                $conversation->participant_one_id === $chatMessage->sender_id) {
                // Participant one adalah sender, jadi participant two adalah receiver
                $receiverType = $conversation->participant_two_type;
                $receiverId = $conversation->participant_two_id;
            }
            // Check participant two - jika adalah sender, maka participant one adalah receiver
            elseif ($conversation->participant_two_type === $chatMessage->sender_type &&
                     $conversation->participant_two_id === $chatMessage->sender_id) {
                // Participant two adalah sender, jadi participant one adalah receiver
                $receiverType = $conversation->participant_one_type;
                $receiverId = $conversation->participant_one_id;
            }

            // Manual load receiver model
            if ($receiverType && $receiverId) {
                $receiver = match ($receiverType) {
                    User::class, 'App\Models\User' => User::find($receiverId),
                    Kurir::class, 'App\Models\Kurir' => Kurir::find($receiverId),
                    Pelanggan::class, 'App\Models\Pelanggan' => Pelanggan::find($receiverId),
                    default => null,
                };
                $receiverRole = $this->getRoleFromType($receiverType);
            }

            if (! $receiver || ! $receiverRole) {
                Log::warning('ChatMessageObserver: Receiver not found', [
                    'message_id' => $chatMessage->id,
                    'conversation_id' => $conversation->id,
                ]);

                return;
            }

            // Dapatkan nama sender
            $senderName = $this->getSenderName($chatMessage->sender);

            // Buat notification title & body
            $title = "Pesan dari {$senderName}";
            $body = $this->truncateMessage($chatMessage->message, 100);

            // Jika ada attachment, tambahkan info
            if ($chatMessage->hasAttachment()) {
                $body = "{$chatMessage->file_name}";
            }

            // Tentukan URL berdasarkan role receiver
            $chatUrl = $this->getChatUrl($receiverRole, $conversation->id);

            // Kirim notifikasi ke receiver
            $sent = FirebaseHelper::sendToUser(
                role: $receiverRole,
                userId: $receiver->id,
                title: $title,
                message: $body,
                data: [
                    'type' => 'chat_message',
                    'conversation_id' => $conversation->id,
                    'message_id' => $chatMessage->id,
                    'sender_name' => $senderName,
                    'url' => $chatUrl,
                ]
            );

            if ($sent) {
                Log::info('ChatMessageObserver: Notification sent', [
                    'message_id' => $chatMessage->id,
                    'receiver_role' => $receiverRole,
                    'receiver_id' => $receiver->id,
                ]);
            }

        } catch (\Exception $e) {
            // Log error tapi jangan throw exception supaya tidak mengganggu proses create message
            Log::error('ChatMessageObserver: Error sending notification', [
                'message_id' => $chatMessage->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get role name from model type
     */
    protected function getRoleFromType(string $type): ?string
    {
        return match ($type) {
            User::class, 'App\Models\User' => 'user',
            Kurir::class, 'App\Models\Kurir' => 'kurir',
            Pelanggan::class, 'App\Models\Pelanggan' => 'pelanggan',
            default => null,
        };
    }

    /**
     * Get sender name from sender model
     */
    protected function getSenderName($sender): string
    {
        if (! $sender) {
            return 'Unknown';
        }

        // Try common name attributes
        if (isset($sender->name)) {
            return $sender->name;
        }

        if (isset($sender->nama)) {
            return $sender->nama;
        }

        if (isset($sender->email)) {
            return $sender->email;
        }

        return 'User';
    }

    /**
     * Truncate message untuk notification
     */
    protected function truncateMessage(?string $message, int $limit = 100): string
    {
        if (! $message) {
            return 'Sent a message';
        }

        if (mb_strlen($message) <= $limit) {
            return $message;
        }

        return mb_substr($message, 0, $limit).'...';
    }

    /**
     * Get chat URL berdasarkan role
     */
    protected function getChatUrl(string $role, int $conversationId): string
    {
        return match ($role) {
            'user' => "/management/chat/{$conversationId}",
            'kurir' => "/kurir/chat/{$conversationId}",
            'pelanggan' => "/pelanggan/chat/{$conversationId}",
            default => "/chat/{$conversationId}",
        };
    }
}

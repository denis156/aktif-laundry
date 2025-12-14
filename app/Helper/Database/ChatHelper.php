<?php

declare(strict_types=1);

namespace App\Helper\Database;

use Exception;
use App\Models\User;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

// ! Helper untuk mengelola Chat/Conversation
//
// ? Mengelola conversation antara User, Kurir, dan Pelanggan
// ? Support file upload, read receipts, dan soft delete

class ChatHelper
{
    // * File upload constants (in KB)
    public const MAX_FILE_SIZE_KB = 5120; // 5 MB

    public const ALLOWED_FILE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    // * Participant types
    public const PARTICIPANT_USER = User::class;

    public const PARTICIPANT_KURIR = Kurir::class;

    public const PARTICIPANT_PELANGGAN = Pelanggan::class;

    /**
     * Find or create conversation antara 2 participant
     */
    public static function findOrCreateConversation(
        string $participantOneType,
        int $participantOneId,
        string $participantTwoType,
        int $participantTwoId
    ): Conversation {
        try {
            // Cari conversation yang sudah ada (bisa dalam urutan apapun)
            $conversation = Conversation::where(function ($query) use ($participantOneType, $participantOneId, $participantTwoType, $participantTwoId) {
                $query->where('participant_one_type', $participantOneType)
                    ->where('participant_one_id', $participantOneId)
                    ->where('participant_two_type', $participantTwoType)
                    ->where('participant_two_id', $participantTwoId);
            })->orWhere(function ($query) use ($participantOneType, $participantOneId, $participantTwoType, $participantTwoId) {
                $query->where('participant_one_type', $participantTwoType)
                    ->where('participant_one_id', $participantTwoId)
                    ->where('participant_two_type', $participantOneType)
                    ->where('participant_two_id', $participantOneId);
            })->first();

            // Jika belum ada, create new conversation
            if (! $conversation) {
                $conversation = Conversation::create([
                    'participant_one_type' => $participantOneType,
                    'participant_one_id' => $participantOneId,
                    'participant_two_type' => $participantTwoType,
                    'participant_two_id' => $participantTwoId,
                ]);

                Log::info('ChatHelper: New conversation created', [
                    'conversation_id' => $conversation->id,
                    'participant_one' => "{$participantOneType}#{$participantOneId}",
                    'participant_two' => "{$participantTwoType}#{$participantTwoId}",
                ]);
            }

            return $conversation;
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to find or create conversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Send message dalam conversation
     */
    public static function sendMessage(
        Conversation $conversation,
        string $senderType,
        int $senderId,
        string $message,
        ?array $file = null
    ): ChatMessage {
        try {
            DB::beginTransaction();

            $data = [
                'conversation_id' => $conversation->id,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'message' => $message,
            ];

            // Handle file upload jika ada
            if ($file !== null) {
                $fileData = self::handleFileUpload($file);
                $data = array_merge($data, $fileData);
            }

            // Create chat message
            $chatMessage = ChatMessage::create($data);

            // Update last_message_at di conversation
            $conversation->update([
                'last_message_at' => now(),
            ]);

            DB::commit();

            Log::info('ChatHelper: Message sent', [
                'message_id' => $chatMessage->id,
                'conversation_id' => $conversation->id,
                'sender' => "{$senderType}#{$senderId}",
                'has_attachment' => $chatMessage->hasAttachment(),
            ]);

            return $chatMessage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ChatHelper: Failed to send message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle file upload untuk chat attachment
     */
    private static function handleFileUpload(array $file): array
    {
        try {
            // Validate file
            if (! isset($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) {
                throw new Exception('Invalid file upload');
            }

            // Check file size (dalam bytes)
            if ($file['size'] > self::MAX_FILE_SIZE_KB * 1024) {
                throw new Exception('File size exceeds maximum limit of '.self::MAX_FILE_SIZE_KB.' KB');
            }

            // Check file type
            $mimeType = mime_content_type($file['tmp_name']);
            if (! in_array($mimeType, self::ALLOWED_FILE_TYPES, true)) {
                throw new Exception('File type not allowed');
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('chat_', true).'.'.$extension;

            // Store file
            $path = Storage::disk('public')->putFileAs('chat-attachments', $file['tmp_name'], $filename);

            return [
                'file_path' => $path,
                'file_name' => $file['name'],
                'file_type' => $mimeType,
                'file_size' => $file['size'],
            ];
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to upload file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark all messages as read dalam conversation untuk specific participant
     */
    public static function markAllMessagesAsRead(
        Conversation $conversation,
        string $participantType,
        int $participantId
    ): int {
        try {
            // Mark semua unread messages yang bukan dari participant ini
            $updated = $conversation->messages()
                ->whereNull('read_at')
                ->where(function ($query) use ($participantType, $participantId) {
                    $query->where('sender_type', '!=', $participantType)
                        ->orWhere('sender_id', '!=', $participantId);
                })
                ->update(['read_at' => now()]);

            if ($updated > 0) {
                Log::info('ChatHelper: Messages marked as read', [
                    'conversation_id' => $conversation->id,
                    'participant' => "{$participantType}#{$participantId}",
                    'count' => $updated,
                ]);
            }

            return $updated;
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to mark messages as read', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all conversations untuk specific participant
     */
    public static function getConversationsFor(string $participantType, int $participantId): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return Conversation::where(function ($query) use ($participantType, $participantId) {
                $query->where('participant_one_type', $participantType)
                    ->where('participant_one_id', $participantId);
            })->orWhere(function ($query) use ($participantType, $participantId) {
                $query->where('participant_two_type', $participantType)
                    ->where('participant_two_id', $participantId);
            })
                ->with(['participantOne', 'participantTwo', 'latestMessage'])
                ->orderByDesc('last_message_at')
                ->get();
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to get conversations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get messages dalam conversation dengan pagination
     */
    public static function getMessages(Conversation $conversation, int $limit = 50, ?int $offset = null): \Illuminate\Database\Eloquent\Collection
    {
        try {
            $query = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($offset !== null) {
                $query->offset($offset);
            }

            return $query->get()->reverse()->values();
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to get messages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete message (soft delete)
     */
    public static function deleteMessage(ChatMessage $message): bool
    {
        try {
            $deleted = $message->delete();

            if ($deleted) {
                Log::info('ChatHelper: Message deleted', [
                    'message_id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                ]);
            }

            return (bool) $deleted;
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to delete message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get unread count untuk specific participant
     */
    public static function getUnreadCount(string $participantType, int $participantId): int
    {
        try {
            // Get all conversations untuk participant
            $conversationIds = Conversation::where(function ($query) use ($participantType, $participantId) {
                $query->where('participant_one_type', $participantType)
                    ->where('participant_one_id', $participantId);
            })->orWhere(function ($query) use ($participantType, $participantId) {
                $query->where('participant_two_type', $participantType)
                    ->where('participant_two_id', $participantId);
            })->pluck('id');

            // Count unread messages yang bukan dari participant ini
            return ChatMessage::whereIn('conversation_id', $conversationIds)
                ->whereNull('read_at')
                ->where(function ($query) use ($participantType, $participantId) {
                    $query->where('sender_type', '!=', $participantType)
                        ->orWhere('sender_id', '!=', $participantId);
                })
                ->count();
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to get unread count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 0;
        }
    }

    /**
     * Validate participant type
     */
    public static function isValidParticipantType(string $type): bool
    {
        return in_array($type, [
            self::PARTICIPANT_USER,
            self::PARTICIPANT_KURIR,
            self::PARTICIPANT_PELANGGAN,
        ], true);
    }

    /**
     * Get participant model dari type dan id
     */
    public static function getParticipantModel(string $type, int $id): ?Model
    {
        try {
            return match ($type) {
                self::PARTICIPANT_USER => User::find($id),
                self::PARTICIPANT_KURIR => Kurir::find($id),
                self::PARTICIPANT_PELANGGAN => Pelanggan::find($id),
                default => null,
            };
        } catch (Exception $e) {
            Log::error('ChatHelper: Failed to get participant model', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Validation rules untuk send message
     */
    public static function sendMessageValidationRules(): array
    {
        return [
            'message' => 'required|string|max:5000',
            'file' => 'nullable|file|max:'.self::MAX_FILE_SIZE_KB.'|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ];
    }
}

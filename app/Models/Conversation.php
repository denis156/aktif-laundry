<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

// ! Model Conversation - Percakapan antara 2 participant
//
// ? Menggunakan polymorphic relationship untuk support User, Kurir, dan Pelanggan
// ? Setiap conversation memiliki 2 participant (one-to-one chat)

class Conversation extends Model
{
    protected $fillable = [
        'participant_one_type',
        'participant_one_id',
        'participant_two_type',
        'participant_two_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    // * Polymorphic relationship untuk participant one
    public function participantOne(): MorphTo
    {
        return $this->morphTo('participant_one');
    }

    // * Polymorphic relationship untuk participant two
    public function participantTwo(): MorphTo
    {
        return $this->morphTo('participant_two');
    }

    // * Relationship ke chat messages
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    // * Get latest message
    public function latestMessage(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latest();
    }

    // * Get unread messages count for specific participant
    public function unreadMessagesFor(string $participantType, int $participantId): int
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where(function ($query) use ($participantType, $participantId) {
                $query->where('sender_type', '!=', $participantType)
                    ->orWhere('sender_id', '!=', $participantId);
            })
            ->count();
    }

    // * Get other participant (bukan diri sendiri)
    public function getOtherParticipant(string $currentType, int $currentId): ?Model
    {
        if ($this->participant_one_type === $currentType && $this->participant_one_id === $currentId) {
            return $this->participantTwo;
        }

        if ($this->participant_two_type === $currentType && $this->participant_two_id === $currentId) {
            return $this->participantOne;
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model ChatMessage - Pesan dalam conversation
//
// ? Support soft delete, file attachment, dan read receipts
// ? Sender menggunakan polymorphic relationship untuk support User, Kurir, dan Pelanggan

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    // * Relationship ke conversation
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    // * Polymorphic relationship untuk sender
    public function sender(): MorphTo
    {
        return $this->morphTo('sender');
    }

    // * Mark message as read
    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    // * Check if message has attachment
    public function hasAttachment(): bool
    {
        return $this->file_path !== null;
    }

    // * Get file size in human readable format
    public function getFileSizeFormatted(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    // * Check if message is read
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}

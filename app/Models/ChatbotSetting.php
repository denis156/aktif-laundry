<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSetting extends Model
{
    /** @use HasFactory<\Database\Factories\ChatbotSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'system_prompt',
        'is_active',
        'enable_chatbot',
        'max_tokens',
        'temperature',
        'timeout',
        'fallback_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enable_chatbot' => 'boolean',
            'max_tokens' => 'integer',
            'temperature' => 'float',
            'timeout' => 'integer',
        ];
    }

    /**
     * Get the active chatbot setting
     */
    public static function getActive(): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->where('enable_chatbot', true)
            ->first();
    }

    /**
     * Deactivate all other settings when this one is activated
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $setting) {
            if ($setting->is_active) {
                // Deactivate all other settings
                self::query()
                    ->where('id', '!=', $setting->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}

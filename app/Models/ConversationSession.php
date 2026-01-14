<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationSession extends Model
{
    protected $fillable = [
        'phone_number',
        'flow_type',
        'current_step',
        'collected_data',
        'last_interaction_at',
        'expires_at',
    ];

    protected $casts = [
        'collected_data' => 'array',
        'last_interaction_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get or create active session for a phone number
     */
    public static function getOrCreateSession(string $phoneNumber, string $flowType = 'inquiry'): self
    {
        // Try to find active session (not expired)
        $session = self::query()
            ->where('phone_number', $phoneNumber)
            ->where('current_step', '!=', 'completed')
            ->where('current_step', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($session) {
            // Update last interaction time
            $session->update(['last_interaction_at' => now()]);

            return $session;
        }

        // Create new session (expires in 30 minutes of inactivity)
        return self::create([
            'phone_number' => $phoneNumber,
            'flow_type' => $flowType,
            'current_step' => 'ask_name',
            'collected_data' => [],
            'last_interaction_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    /**
     * Update session data and move to next step
     */
    public function updateData(array $data, ?string $nextStep = null): void
    {
        $collectedData = $this->collected_data ?? [];
        $collectedData = array_merge($collectedData, $data);

        $updates = [
            'collected_data' => $collectedData,
            'last_interaction_at' => now(),
            'expires_at' => now()->addMinutes(30), // Extend expiry
        ];

        if ($nextStep) {
            $updates['current_step'] = $nextStep;
        }

        $this->update($updates);
    }

    /**
     * Mark session as completed
     */
    public function markCompleted(): void
    {
        $this->update([
            'current_step' => 'completed',
            'last_interaction_at' => now(),
        ]);
    }

    /**
     * Mark session as cancelled
     */
    public function markCancelled(): void
    {
        $this->update([
            'current_step' => 'cancelled',
            'last_interaction_at' => now(),
        ]);
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if session is in registration flow
     */
    public function isRegistrationFlow(): bool
    {
        return $this->flow_type === 'registration';
    }

    /**
     * Get collected data value
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return data_get($this->collected_data, $key, $default);
    }
}

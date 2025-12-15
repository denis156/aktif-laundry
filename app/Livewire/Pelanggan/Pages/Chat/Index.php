<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Chat;

use App\Helper\Database\ChatHelper;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chat')]
#[Layout('layouts.pelanggan.app')]
class Index extends Component
{
    public string $search = '';

    public function getConversationsProperty()
    {
        $currentPelanggan = Auth::guard('pelanggan')->user();
        $currentType = Pelanggan::class;

        return ChatHelper::getConversationsFor($currentType, $currentPelanggan->id)
            ->map(function ($conversation) use ($currentType, $currentPelanggan) {
                // Manually load participant
                $otherParticipantType = null;
                $otherParticipantId = null;

                if ($conversation->participant_one_type === $currentType && $conversation->participant_one_id === $currentPelanggan->id) {
                    $otherParticipantType = $conversation->participant_two_type;
                    $otherParticipantId = $conversation->participant_two_id;
                } elseif ($conversation->participant_two_type === $currentType && $conversation->participant_two_id === $currentPelanggan->id) {
                    $otherParticipantType = $conversation->participant_one_type;
                    $otherParticipantId = $conversation->participant_one_id;
                }

                if (! $otherParticipantType || ! $otherParticipantId) {
                    return null;
                }

                $otherParticipant = ChatHelper::getParticipantModel($otherParticipantType, $otherParticipantId);

                if (! $otherParticipant) {
                    return null;
                }

                $latestMsg = $conversation->latestMessage->first();

                $participantType = class_basename($otherParticipantType);
                if ($participantType === 'User') {
                    $participantType = 'Admin';
                }

                return (object) [
                    'id' => $conversation->id,
                    'participant_name' => $otherParticipant->nama ?? $otherParticipant->name,
                    'participant_type' => $participantType,
                    'participant_avatar' => $otherParticipant->avatar_url ?? null,
                    'last_message' => $latestMsg?->message ?? 'Belum ada pesan',
                    'last_message_at' => $conversation->last_message_at ?? $conversation->created_at,
                    'unread_count' => $conversation->unreadMessagesFor($currentType, $currentPelanggan->id),
                    'is_online' => false,
                ];
            })
            ->filter()
            ->filter(function ($conversation) {
                if ($this->search === '') {
                    return true;
                }

                return str_contains(strtolower($conversation->participant_name), strtolower($this->search)) ||
                       str_contains(strtolower($conversation->last_message), strtolower($this->search));
            });
    }

    public function render()
    {
        return view('livewire.pelanggan.pages.chat.index');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Chat;

use App\Helper\Database\ChatHelper;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chat')]
#[Layout('layouts.kurir.app')]
class Index extends Component
{
    public string $search = '';

    public bool $createModal = false;

    public array $participantTypes = [];

    public int|string $participantId = '';

    public function getConversationsProperty()
    {
        $currentKurir = Auth::guard('kurir')->user();
        $currentType = Kurir::class;

        return ChatHelper::getConversationsFor($currentType, $currentKurir->id)
            ->map(function ($conversation) use ($currentType, $currentKurir) {
                // Manually load participant
                $otherParticipantType = null;
                $otherParticipantId = null;

                if ($conversation->participant_one_type === $currentType && $conversation->participant_one_id === $currentKurir->id) {
                    $otherParticipantType = $conversation->participant_two_type;
                    $otherParticipantId = $conversation->participant_two_id;
                } elseif ($conversation->participant_two_type === $currentType && $conversation->participant_two_id === $currentKurir->id) {
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
                    'unread_count' => $conversation->unreadMessagesFor($currentType, $currentKurir->id),
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

    public function getParticipantsProperty()
    {
        if (empty($this->participantTypes)) {
            return collect();
        }

        $currentKurir = Auth::guard('kurir')->user();
        $currentType = Kurir::class;

        $participants = collect();

        foreach ($this->participantTypes as $type) {
            $typeClass = match ($type) {
                'User' => User::class,
                'Pelanggan' => Pelanggan::class,
                default => null,
            };

            if (! $typeClass) {
                continue;
            }

            // Get available participants menggunakan ChatHelper
            $availableParticipants = ChatHelper::getAvailableParticipants(
                $currentType,
                $currentKurir->id,
                $typeClass
            );

            $items = $availableParticipants->map(function ($participant) use ($type, $typeClass) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->nama ?? $participant->name,
                    'type' => $type === 'User' ? 'Admin' : $type,
                    'type_class' => $typeClass,
                    'avatar' => $participant->avatar_url ?? null,
                    'phone' => $participant->no_hp ?? '-',
                    'address' => $participant->alamat ?? '-',
                ];
            });

            $participants = $participants->merge($items);
        }

        return $participants;
    }

    public function createConversation()
    {
        $this->validate([
            'participantTypes' => 'required|array|min:1',
            'participantTypes.*' => 'in:User,Pelanggan',
            'participantId' => 'required|integer',
        ]);

        $currentKurir = Auth::guard('kurir')->user();

        // Tentukan tipe participant berdasarkan ID yang dipilih
        $participantTypeClass = null;
        $participantId = (int) $this->participantId;

        foreach ($this->participantTypes as $type) {
            $exists = match ($type) {
                'User' => User::where('id', $participantId)->exists(),
                'Pelanggan' => Pelanggan::where('id', $participantId)->exists(),
                default => false,
            };

            if ($exists) {
                $participantTypeClass = match ($type) {
                    'User' => User::class,
                    'Pelanggan' => Pelanggan::class,
                };
                break;
            }
        }

        if (! $participantTypeClass) {
            $this->addError('participantId', 'Participant tidak ditemukan.');

            return;
        }

        $conversation = ChatHelper::findOrCreateConversation(
            Kurir::class,
            $currentKurir->id,
            $participantTypeClass,
            $participantId
        );

        $this->createModal = false;
        $this->reset(['participantTypes', 'participantId']);

        return $this->redirect(route('chat-room.kurir', $conversation->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.kurir.pages.chat.index');
    }
}

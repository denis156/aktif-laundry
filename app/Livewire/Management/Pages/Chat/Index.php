<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Chat;

use App\Helper\Database\ChatHelper;
use App\Models\Kurir;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Daftar Chat')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public string $filterType = '';

    public bool $createModal = false;

    public array $participantTypes = [];

    public int|string $participantId = '';

    public bool $deleteModal = false;

    public ?int $conversationToDelete = null;

    public string $conversationToDeleteName = '';

    public function getConversationsProperty()
    {
        $currentUser = Auth::user();
        $currentType = User::class;

        return ChatHelper::getConversationsFor($currentType, $currentUser->id)
            ->map(function ($conversation) use ($currentType, $currentUser) {
                // Manually load participant
                $otherParticipantType = null;
                $otherParticipantId = null;

                if ($conversation->participant_one_type === $currentType && $conversation->participant_one_id === $currentUser->id) {
                    $otherParticipantType = $conversation->participant_two_type;
                    $otherParticipantId = $conversation->participant_two_id;
                } elseif ($conversation->participant_two_type === $currentType && $conversation->participant_two_id === $currentUser->id) {
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
                    $participantType = 'Staff';
                }

                return (object) [
                    'id' => $conversation->id,
                    'participant_name' => $otherParticipant->nama ?? $otherParticipant->name,
                    'participant_type' => $participantType,
                    'participant_avatar' => $otherParticipant->avatar_url ?? null,
                    'last_message' => $latestMsg?->message ?? 'Belum ada pesan',
                    'last_message_at' => $conversation->last_message_at ?? $conversation->created_at,
                    'unread_count' => $conversation->unreadMessagesFor($currentType, $currentUser->id),
                    'is_online' => false, // TODO: implement online status
                ];
            })
            ->filter()
            ->filter(function ($conversation) {
                if ($this->search === '') {
                    return true;
                }

                return str_contains(strtolower($conversation->participant_name), strtolower($this->search)) ||
                       str_contains(strtolower($conversation->last_message), strtolower($this->search));
            })
            ->filter(function ($conversation) {
                if ($this->filterType === '') {
                    return true;
                }

                return $conversation->participant_type === $this->filterType;
            });
    }

    public function getParticipantsProperty()
    {
        if (empty($this->participantTypes)) {
            return collect();
        }

        $currentUser = Auth::user();
        $currentType = User::class;

        $participants = collect();

        foreach ($this->participantTypes as $type) {
            $typeClass = match ($type) {
                'User' => User::class,
                'Kurir' => Kurir::class,
                'Pelanggan' => Pelanggan::class,
                default => null,
            };

            if (! $typeClass) {
                continue;
            }

            // Get available participants menggunakan ChatHelper
            $availableParticipants = ChatHelper::getAvailableParticipants(
                $currentType,
                $currentUser->id,
                $typeClass
            );

            // Filter user sendiri jika tipe User
            if ($type === 'User') {
                $availableParticipants = $availableParticipants->where('id', '!=', Auth::id());
            }

            $items = $availableParticipants->map(function ($participant) use ($type, $typeClass) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->nama ?? $participant->name,
                    'type' => $type === 'User' ? 'Staf' : $type,
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
            'participantTypes.*' => 'in:User,Kurir,Pelanggan',
            'participantId' => 'required|integer',
        ]);

        $currentUser = Auth::user();

        // Tentukan tipe participant berdasarkan ID yang dipilih
        $participantTypeClass = null;
        $participantId = (int) $this->participantId;

        foreach ($this->participantTypes as $type) {
            $exists = match ($type) {
                'User' => User::where('id', $participantId)->exists(),
                'Kurir' => Kurir::where('id', $participantId)->exists(),
                'Pelanggan' => Pelanggan::where('id', $participantId)->exists(),
                default => false,
            };

            if ($exists) {
                $participantTypeClass = match ($type) {
                    'User' => User::class,
                    'Kurir' => Kurir::class,
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
            User::class,
            $currentUser->id,
            $participantTypeClass,
            $participantId
        );

        $this->createModal = false;
        $this->reset(['participantTypes', 'participantId']);

        return $this->redirect(route('chat.room', $conversation->id), navigate: true);
    }

    public function confirmDelete(int $conversationId): void
    {
        $conversation = $this->conversations->firstWhere('id', $conversationId);

        if ($conversation) {
            $this->conversationToDelete = $conversationId;
            $this->conversationToDeleteName = $conversation->participant_name;
            $this->deleteModal = true;
        }
    }

    public function deleteConversation(): void
    {
        if (! $this->conversationToDelete) {
            return;
        }

        $conversation = ChatHelper::getConversationsFor(User::class, Auth::id())
            ->firstWhere('id', $this->conversationToDelete);

        if ($conversation) {
            $participantName = $this->conversationToDeleteName;

            // Hapus conversation (observer akan handle file deletion dan messages)
            $conversation->delete();

            $this->deleteModal = false;
            $this->reset(['conversationToDelete', 'conversationToDeleteName']);

            $this->success("Chat dengan {$participantName} berhasil dihapus!", position: 'toast-bottom');
        }
    }

    public function clear(): void
    {
        $this->search = '';
        $this->filterType = '';
    }

    public function render()
    {
        return view('livewire.management.pages.chat.index');
    }
}

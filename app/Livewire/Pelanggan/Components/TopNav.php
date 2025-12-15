<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Components;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\ChatHelper;
use App\Models\Pelanggan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TopNav extends Component
{
    #[Computed]
    public function pelanggan()
    {
        return auth('pelanggan')->user();
    }

    #[Computed]
    public function avatarUrl(): string
    {
        $pelanggan = $this->pelanggan;

        if (! $pelanggan) {
            return AvatarPlaceholder::generate('Guest', 256);
        }

        return AvatarPlaceholder::getAvatarOrPlaceholder(
            $pelanggan->avatar_url,
            $pelanggan->nama,
            256
        );
    }

    #[Computed]
    public function unreadMessagesCount(): int
    {
        $pelanggan = $this->pelanggan;

        if (! $pelanggan) {
            return 0;
        }

        return ChatHelper::getConversationsFor(Pelanggan::class, $pelanggan->id)
            ->sum(function ($conversation) use ($pelanggan) {
                return $conversation->unreadMessagesFor(Pelanggan::class, $pelanggan->id);
            });
    }

    public function render()
    {
        return view('livewire.pelanggan.components.top-nav');
    }
}

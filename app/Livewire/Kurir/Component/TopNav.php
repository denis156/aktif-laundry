<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Component;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\ChatHelper;
use App\Models\Kurir;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TopNav extends Component
{
    #[Computed]
    public function kurir()
    {
        return auth('kurir')->user();
    }

    #[Computed]
    public function avatarUrl(): string
    {
        $kurir = $this->kurir;

        if (! $kurir) {
            return AvatarPlaceholder::generate('Guest', 256);
        }

        return AvatarPlaceholder::getAvatarOrPlaceholder(
            $kurir->avatar_url,
            $kurir->nama,
            256
        );
    }

    #[Computed]
    public function unreadMessagesCount(): int
    {
        $kurir = $this->kurir;

        if (! $kurir) {
            return 0;
        }

        return ChatHelper::getConversationsFor(Kurir::class, $kurir->id)
            ->sum(function ($conversation) use ($kurir) {
                return $conversation->unreadMessagesFor(Kurir::class, $kurir->id);
            });
    }

    public function render()
    {
        return view('livewire.kurir.component.top-nav');
    }
}

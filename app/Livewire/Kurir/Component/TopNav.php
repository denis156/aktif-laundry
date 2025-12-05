<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Component;

use App\Helper\AvatarPlaceholder;
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

    public function render()
    {
        return view('livewire.kurir.component.top-nav');
    }
}

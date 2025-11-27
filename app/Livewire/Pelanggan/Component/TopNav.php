<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\AvatarPlaceholder;
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

    public function render()
    {
        return view('livewire.pelanggan.component.top-nav');
    }
}

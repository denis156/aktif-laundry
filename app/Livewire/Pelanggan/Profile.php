<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profil Saya')]
#[Layout('layouts.pelanggan.app')]
class Profile extends Component
{
    public ?string $nama = null;

    public ?string $noHp = null;

    public ?string $email = null;

    public ?string $alamat = null;

    public ?string $kodePelanggan = null;

    public ?string $avatarUrl = null;

    /**
     * Mount component and load pelanggan data
     */
    public function mount(): void
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        if ($pelanggan) {
            $this->nama = $pelanggan->nama;
            $this->noHp = $pelanggan->no_hp;
            $this->email = $pelanggan->email;
            $this->alamat = $pelanggan->alamat;
            $this->kodePelanggan = $pelanggan->kode_pelanggan;
            // TODO: Implement avatar URL from metadata when needed
            $this->avatarUrl = null;
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.profile');
    }
}

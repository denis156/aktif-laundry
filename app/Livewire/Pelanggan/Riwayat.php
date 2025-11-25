<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Riwayat Transaksi')]
#[Layout('layouts.pelanggan.app')]
class Riwayat extends Component
{
    public function render(): mixed
    {
        return view('livewire.pelanggan.riwayat');
    }
}

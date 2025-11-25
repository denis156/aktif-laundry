<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pesan')]
#[Layout('layouts.pelanggan.app')]
class Pesan extends Component
{
    public function render(): mixed
    {
        return view('livewire.pelanggan.pesan');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengiriman Kurir')]
#[Layout('layouts.kurir.app')]
class Pengiriman extends Component
{
    public function render()
    {
        return view('livewire.kurir.pengiriman');
    }
}

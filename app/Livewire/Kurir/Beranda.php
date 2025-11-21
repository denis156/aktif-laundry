<?php

namespace App\Livewire\Kurir;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Beranda Kurir')]
#[Layout('layouts.kurir.app')]
class Beranda extends Component
{
    public function render()
    {
        return view('livewire.kurir.beranda');
    }
}

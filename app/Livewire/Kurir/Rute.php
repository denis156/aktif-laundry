<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Rute Kurir')]
#[Layout('layouts.kurir.app')]
class Rute extends Component
{
    public function render()
    {
        return view('livewire.kurir.rute');
    }
}
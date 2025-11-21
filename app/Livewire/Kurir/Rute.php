<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rute Kurir')]
#[Layout('layouts.kurir.app')]
class Rute extends Component
{
    public function render()
    {
        return view('livewire.kurir.rute');
    }
}

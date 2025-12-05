<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Aktifitas Kurir')]
#[Layout('layouts.kurir.app')]
class Aktifitas extends Component
{
    public function render()
    {
        return view('livewire.kurir.aktifitas');
    }
}

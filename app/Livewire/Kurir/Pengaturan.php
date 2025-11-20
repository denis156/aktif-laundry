<?php

namespace App\Livewire\Kurir;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Beranda Kurir')]
#[Layout('layouts.kurir.app')]
class Pengaturan extends Component
{
    public bool $modalUbahNama = false;

    public function render()
    {
        return view('livewire.kurir.pengaturan');
    }
}

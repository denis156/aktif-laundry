<?php

namespace App\Livewire\Component;

use Livewire\Component;

class DarkModeToggle extends Component
{
    public string $toggleClass = 'toggle-success';

    public bool $right = false;

    public function mount(
        string $toggleClass = 'toggle-success',
        bool $right = false
    ) {
        $this->toggleClass = $toggleClass;
        $this->right = $right;
    }

    public function render()
    {
        return view('livewire.component.dark-mode-toggle');
    }
}

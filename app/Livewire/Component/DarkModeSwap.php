<?php

namespace App\Livewire\Component;

use Livewire\Component;

class DarkModeSwap extends Component
{
    public string $trueIcon = 'o-moon';
    public string $falseIcon = 'o-sun';
    public string $iconSize = 'h-6 w-6';
    public string $swapClass = 'swap-rotate';
    public string $swapId = 'dark-mode-swap';

    public function mount(
        string $trueIcon = 'o-moon',
        string $falseIcon = 'o-sun',
        string $iconSize = 'h-6 w-6',
        string $swapClass = 'swap-rotate',
        string $swapId = 'dark-mode-swap'
    ) {
        $this->trueIcon = $trueIcon;
        $this->falseIcon = $falseIcon;
        $this->iconSize = $iconSize;
        $this->swapClass = $swapClass;
        $this->swapId = $swapId;
    }

    public function render()
    {
        return view('livewire.component.dark-mode-swap');
    }
}

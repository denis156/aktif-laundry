<?php

declare(strict_types=1);

namespace App\Livewire\Component;

use Livewire\Component;

class Loading extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div x-data="{ isNavigating: false }"
            x-init="
                window.addEventListener('livewire:navigate', () => isNavigating = true);
                window.addEventListener('livewire:navigated', () => isNavigating = false);
            "
            x-show="isNavigating"
            x-transition.opacity.duration.300ms
            class="fixed inset-0 z-40 flex items-center justify-center bg-base-200"
            style="display: none;">
                <x-loading class="loading-bars loading-xl text-primary" />
        </div>
        HTML;
    }
}

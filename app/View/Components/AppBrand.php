<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <a href="/" wire:navigate>
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-2 w-fit">
                            <x-icon name="o-arrow-uturn-left" class="w-6 -mb-1.5 text-red-700" />
                            <span class="font-bold text-lg mt-2 me-3 bg-linear-to-r from-red-700 via-red-500 to-red-300 bg-clip-text text-transparent ">
                                AKTIF LAUNDRY
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-5 mt-5 mb-1 h-7">
                        <x-icon name="o-arrow-uturn-right" class="w-6 -mb-1.5 text-red-700" />
                    </div>
                </a>
            HTML;
    }
}

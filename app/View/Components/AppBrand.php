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
                <a href="/" wire:navigate class="flex items-center gap-3 w-fit">
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-3 w-fit">
                            <img src="{{ asset('images/Logo.png') }}" alt="{{ config('app.name') }}" class="w-12 h-12 object-contain" />
                            <span class="font-bold text-xl mt-1 bg-linear-to-bl from-primary/38 via-primary/68 to-primary/28 bg-clip-text text-transparent whitespace-nowrap">
                                {{ config('app.name') }}
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-5 mt-5 mb-1 h-8 w-8">
                        <img src="{{ asset('images/Logo.png') }}" alt="{{ config('app.name') }}" class="w-12 h-12 object-contain" />
                    </div>
                </a>
            HTML;
    }
}

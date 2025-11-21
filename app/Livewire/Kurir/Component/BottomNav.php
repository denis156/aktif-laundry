<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Component;

use Livewire\Component;
use Illuminate\Support\Facades\Route;

class BottomNav extends Component
{
    /**
     * Array of navigation items with their properties.
     */
    public array $navigationItems = [
        [
            'name' => 'Beranda',
            'route' => 'beranda.kurir',
            'icon' => 'iconpark.dashboardtwo-o',
            'icon_size' => 'size-2',
        ],
        [
            'name' => 'Pesanan',
            'route' => 'pesanan.kurir',
            'icon' => 'iconpark.listview-o',
            'icon_size' => 'size-4',
        ],
        [
            'name' => 'Rute',
            'route' => 'rute.kurir',
            'icon' => 'iconpark.maproadtwo-o',
            'icon_size' => 'size-4',
        ],
        [
            'name' => 'Info',
            'route' => 'info.kurir',
            'icon' => 'iconpark.info-o',
            'icon_size' => 'size-4',
        ],
        [
            'name' => 'Pengaturan',
            'route' => 'pengaturan.kurir',
            'icon' => 'iconpark.setting-o',
            'icon_size' => 'size-4',
        ],
    ];

    /**
     * Get the current active route name.
     */
    public function getCurrentRoute(): string
    {
        return Route::currentRouteName();
    }

    /**
     * Check if a navigation item is active.
     */
    public function isActive(string $routeName): bool
    {
        $currentRoute = $this->getCurrentRoute();

        // Exact match
        if ($currentRoute === $routeName) {
            return true;
        }

        // Handle base route case (beranda.kurir matches /kurir)
        if ($routeName === 'beranda.kurir' && $currentRoute === 'beranda.kurir') {
            return true;
        }

        return false;
    }

    /**
     * Get the CSS classes for a navigation item based on active state.
     */
    public function getNavButtonClasses(string $routeName): string
    {
        $baseClasses = '';

        if ($this->isActive($routeName)) {
            return $baseClasses . ' dock-active';
        }

        return $baseClasses;
    }

    /**
     * Get the CSS classes for navigation item content based on active state.
     */
    public function getNavContentClasses(string $routeName): array
    {
        if ($this->isActive($routeName)) {
            return [
                'icon' => 'text-neutral-content',
                'label' => 'text-neutral-content font-bold',
            ];
        }

        return [
            'icon' => 'text-neutral',
            'label' => 'text-neutral',
        ];
    }

    public function render()
    {
        return view('livewire.kurir.component.bottom-nav', [
            'navigationItems' => $this->navigationItems,
        ]);
    }
}

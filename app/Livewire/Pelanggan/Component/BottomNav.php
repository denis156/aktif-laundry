<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class BottomNav extends Component
{
    /**
     * Array of navigation items with their properties.
     */
    public array $navigationItems = [
        [
            'name' => 'Beranda',
            'route' => 'beranda.pelanggan',
            'icon' => 'iconpark.dashboardtwo-o',
            'icon_size' => 'size-2',
        ],
        [
            'name' => 'Pesan',
            'route' => 'pesanan.pelanggan',
            'icon' => 'iconpark.listview-o',
            'icon_size' => 'size-4',
        ],
        [
            'name' => 'Riwayat',
            'route' => 'riwayat.pelanggan',
            'icon' => 'iconpark.history-o',
            'icon_size' => 'size-4',
        ],
        [
            'name' => 'Pengaturan',
            'route' => 'pengaturan.pelanggan',
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

        // Check if current route is a sub-route of the navigation item
        // For example: 'pesanan-form.pelanggan' should activate 'pesanan.pelanggan'
        // and 'profile.pelanggan' should activate 'pengaturan.pelanggan'
        $routeMapping = [
            'pesanan.pelanggan' => ['pesanan-form.pelanggan', 'edit-pesanan.pelanggan'],
            'pengaturan.pelanggan' => ['profile.pelanggan'],
            'riwayat.pelanggan' => ['detail-pesanan.pelanggan'],
        ];

        if (isset($routeMapping[$routeName]) && in_array($currentRoute, $routeMapping[$routeName])) {
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
            return $baseClasses.' dock-active';
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
                'icon' => 'text-base-content',
                'label' => 'text-base-content font-extrabold',
            ];
        }

        return [
            'icon' => 'text-neutral',
            'label' => 'text-neutral font-thin',
        ];
    }

    public function render()
    {
        return view('livewire.pelanggan.component.bottom-nav', [
            'navigationItems' => $this->navigationItems,
        ]);
    }
}

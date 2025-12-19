<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class MapsController extends Component
{
    #[Modelable]
    public string $mapId = 'map';

    public string $position = 'top-right';

    public bool $showZoom = true;

    public bool $showLayers = true;

    public bool $showCenter = true;

    public bool $showCompass = true;

    public ?string $currentLayer = null;

    public function mount(): void
    {
        // Set default layer if not provided
        if (! $this->currentLayer) {
            $this->currentLayer = 'googleStreet';
        }
    }

    public function getPositionClasses(): string
    {
        return match ($this->position) {
            'top-left' => 'top-20 left-4',
            'top-right' => 'top-20 right-4',
            'bottom-left' => 'bottom-4 left-4',
            'bottom-right' => 'bottom-4 right-4',
            default => 'top-20 right-4',
        };
    }

    public function getLayerMenuPosition(): string
    {
        return match ($this->position) {
            'top-left', 'bottom-left' => 'left-0',
            'top-right', 'bottom-right' => 'right-0',
            default => 'right-0',
        };
    }

    public function render(): View
    {
        return view('livewire.components.maps-controller');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Tracking;

use App\Models\Kurir;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lacak Kurir')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    public array $activeKurir = [];

    public int $activeKurirCount = 0;

    public function mount(): void
    {
        $this->updateActiveKurir();
    }

    /**
     * Update active courier data and dispatch to frontend
     */
    public function updateActiveKurir(): void
    {
        $activeKurir = [];

        // Get all kurir IDs from database
        $kurirIds = Kurir::query()->pluck('id');

        foreach ($kurirIds as $kurirId) {
            $cacheKey = 'kurir_tracking_'.$kurirId;
            $trackingData = Cache::get($cacheKey);

            if ($trackingData) {
                // Only include if updated within last 2 minutes (lebih ketat dari cache expiry)
                $updatedAt = \Carbon\Carbon::parse($trackingData['updated_at']);
                if ($updatedAt->diffInMinutes(now()) <= 2) {
                    $activeKurir[] = $trackingData;
                }
            }
        }

        $this->activeKurir = $activeKurir;
        $this->activeKurirCount = count($activeKurir);

        // Dispatch event to JavaScript with active kurir data
        $this->dispatch('kurir-locations-updated', activeKurir: $activeKurir);
    }

    public function render(): View
    {
        return view('livewire.management.pages.tracking.index');
    }
}

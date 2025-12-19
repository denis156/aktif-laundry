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
    public function getKurirLocationsProperty(): array
    {
        // Get all active kurir
        $kurirList = Kurir::query()
            ->where('status', 'Aktif')
            ->select([
                'id',
                'nama',
                'jenis_kendaraan',
                'no_hp',
                'avatar_url',
            ])
            ->get();

        $activeKurir = [];

        foreach ($kurirList as $kurir) {
            // Check cache for real-time location
            $realtimeCacheKey = 'kurir_realtime_location_'.$kurir->id;
            $cachedData = Cache::get($realtimeCacheKey);

            // Only include kurir yang ada di cache (sedang aktif tracking)
            if ($cachedData) {
                $activeKurir[] = [
                    'id' => $kurir->id,
                    'nama' => $cachedData['nama'] ?? $kurir->nama,
                    'jenis_kendaraan' => $cachedData['jenis_kendaraan'] ?? $kurir->jenis_kendaraan,
                    'latitude' => $cachedData['latitude'],
                    'longitude' => $cachedData['longitude'],
                    'bearing' => $cachedData['bearing'] ?? null,
                    'speed' => $cachedData['speed'] ?? null,
                    'accuracy' => $cachedData['accuracy'] ?? null,
                    'no_hp' => $cachedData['no_hp'] ?? $kurir->no_hp,
                    'avatar_url' => $cachedData['avatar_url'] ?? $kurir->avatar_url,
                    'is_realtime' => true,
                    'updated_at' => $cachedData['updated_at'] ?? null,
                ];
            }
        }

        return $activeKurir;
    }

    public function render(): View
    {
        return view('livewire.management.pages.tracking.index', [
            'totalKurir' => count($this->kurirLocations),
        ]);
    }
}

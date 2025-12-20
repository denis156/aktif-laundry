<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Tracking;

use App\Models\Kurir;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail Lacak Kurir')]
#[Layout('layouts.management.app')]
class Detail extends Component
{
    public ?Kurir $kurir = null;

    public ?float $pelangganLatitude = null;

    public ?float $pelangganLongitude = null;

    public ?string $pelangganNama = null;

    public ?string $pelangganAlamat = null;

    public ?float $kurirLatitude = null;

    public ?float $kurirLongitude = null;

    public ?float $kurirAccuracy = null;

    public ?float $kurirSpeed = null;

    public ?float $kurirBearing = null;

    public ?string $jenisKendaraan = null;

    public string $trackingStatus = 'searching';

    public function mount(int $id): void
    {
        // Load kurir data
        $this->kurir = Kurir::query()->findOrFail($id);

        $this->jenisKendaraan = $this->kurir->jenis_kendaraan;

        // Get tracking data from cache
        $this->updateTrackingData();
    }

    public function updateTrackingData(): void
    {
        if (! $this->kurir) {
            return;
        }

        $cacheKey = 'kurir_tracking_'.$this->kurir->id;
        $trackingData = Cache::get($cacheKey);

        if ($trackingData) {
            // Update tracking data
            $this->kurirLatitude = $trackingData['latitude'] ?? null;
            $this->kurirLongitude = $trackingData['longitude'] ?? null;
            $this->kurirAccuracy = $trackingData['accuracy'] ?? null;
            $this->kurirSpeed = $trackingData['speed'] ?? null;
            $this->kurirBearing = $trackingData['bearing'] ?? null;

            // Update pelanggan data
            $this->pelangganNama = $trackingData['pelanggan_nama'] ?? null;
            $this->pelangganAlamat = $trackingData['pelanggan_alamat'] ?? null;
            $this->pelangganLatitude = $trackingData['pelanggan_latitude'] ?? null;
            $this->pelangganLongitude = $trackingData['pelanggan_longitude'] ?? null;

            $this->trackingStatus = 'active';

            // Dispatch to JavaScript
            $this->dispatch('tracking-data-updated', trackingData: $trackingData);
        } else {
            $this->trackingStatus = 'inactive';
        }
    }

    public function render(): View
    {
        return view('livewire.management.pages.tracking.detail');
    }
}

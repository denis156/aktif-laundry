<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rute Detail')]
#[Layout('layouts.kurir.app')]
class RuteDetail extends Component
{
    public ?Transaksi $transaksi = null;

    public ?float $pelangganLatitude = null;

    public ?float $pelangganLongitude = null;

    public ?string $pelangganNama = null;

    public ?string $pelangganAlamat = null;

    public ?string $pelangganNoHp = null;

    public ?float $kurirLatitude = null;

    public ?float $kurirLongitude = null;

    public ?float $kurirAccuracy = null;

    public string $gpsStatus = 'initializing';

    public bool $hasInitialLocation = false;

    public function mount(int $id): void
    {
        // Load transaksi dengan relasi pelanggan
        $this->transaksi = Transaksi::query()
            ->with('pelanggan')
            ->findOrFail($id);

        // Set data pelanggan untuk map
        if ($this->transaksi->pelanggan) {
            $this->pelangganLatitude = $this->transaksi->pelanggan->latitude
                ? (float) $this->transaksi->pelanggan->latitude
                : null;
            $this->pelangganLongitude = $this->transaksi->pelanggan->longitude
                ? (float) $this->transaksi->pelanggan->longitude
                : null;
            $this->pelangganNama = $this->transaksi->pelanggan->nama;
            $this->pelangganAlamat = $this->transaksi->pelanggan->alamat;
            $this->pelangganNoHp = $this->transaksi->pelanggan->no_hp;
        }

        // Load lokasi kurir terakhir dari database untuk inisialisasi map
        $kurir = auth('kurir')->user();
        if ($kurir) {
            $this->kurirLatitude = $kurir->latitude ? (float) $kurir->latitude : null;
            $this->kurirLongitude = $kurir->longitude ? (float) $kurir->longitude : null;

            // Cek apakah sudah pernah ada lokasi tersimpan
            if ($this->kurirLatitude && $this->kurirLongitude) {
                $this->hasInitialLocation = true;
            }
        }

        // Try to get cached location for this route
        $cacheKey = 'kurir_route_start_'.$kurir?->id.'_'.$id;
        $cachedLocation = Cache::get($cacheKey);

        if ($cachedLocation && ! $this->hasInitialLocation) {
            $this->kurirLatitude = $cachedLocation['latitude'];
            $this->kurirLongitude = $cachedLocation['longitude'];
            $this->hasInitialLocation = true;
        }
    }

    public function updateKurirLocation(float $latitude, float $longitude, float $accuracy): void
    {
        $this->kurirLatitude = $latitude;
        $this->kurirLongitude = $longitude;
        $this->kurirAccuracy = $accuracy;
        $this->gpsStatus = 'active';

        // Cache lokasi awal untuk route ini (pertama kali saja)
        if (! $this->hasInitialLocation) {
            $kurir = auth('kurir')->user();
            $cacheKey = 'kurir_route_start_'.$kurir?->id.'_'.$this->transaksi?->id;

            Cache::put($cacheKey, [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ], now()->addHours(24)); // Cache selama 24 jam

            $this->hasInitialLocation = true;
        }
    }

    public function updateGpsStatus(string $status): void
    {
        $this->gpsStatus = $status;
    }

    public function render(): View
    {
        return view('livewire.kurir.rute-detail');
    }
}

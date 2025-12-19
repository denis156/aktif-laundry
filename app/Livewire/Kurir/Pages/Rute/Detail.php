<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Rute;

use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rute Detail')]
#[Layout('layouts.kurir.app')]
class Detail extends Component
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

    public ?string $jenisKendaraan = null;

    public ?float $kurirSpeed = null;

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
            $this->jenisKendaraan = $kurir->jenis_kendaraan;

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

    public function updateKurirLocation(float $latitude, float $longitude, float $accuracy, ?float $speed = null, ?float $bearing = null): void
    {
        $this->kurirLatitude = $latitude;
        $this->kurirLongitude = $longitude;
        $this->kurirAccuracy = $accuracy;
        $this->kurirSpeed = $speed;
        $this->gpsStatus = 'active';

        $kurir = auth('kurir')->user();

        // Cache lokasi real-time untuk tracking (selalu update)
        if ($kurir) {
            $trackingCacheKey = 'kurir_tracking_'.$kurir->id;

            Cache::put($trackingCacheKey, [
                'kurir_id' => $kurir->id,
                'nama' => $kurir->nama,
                'no_hp' => $kurir->no_hp,
                'jenis_kendaraan' => $kurir->jenis_kendaraan,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'speed' => $speed,
                'bearing' => $bearing,
                'transaksi_id' => $this->transaksi?->id,
                'pelanggan_nama' => $this->pelangganNama,
                'pelanggan_alamat' => $this->pelangganAlamat,
                'pelanggan_latitude' => $this->pelangganLatitude,
                'pelanggan_longitude' => $this->pelangganLongitude,
                'updated_at' => now()->toIso8601String(),
            ], now()->addMinutes(5)); // Cache 5 menit, akan expired jika kurir stop tracking
        }

        // Cache lokasi awal untuk route ini (pertama kali saja)
        if (! $this->hasInitialLocation) {
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
        return view('livewire.kurir.pages.rute.detail');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Pesanan;

use App\Models\Transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lacak Kurir')]
#[Layout('layouts.pelanggan.app')]
class KurirPosition extends Component
{
    public ?Transaksi $transaksi = null;

    public ?float $pelangganLatitude = null;

    public ?float $pelangganLongitude = null;

    public ?string $pelangganNama = null;

    public ?string $pelangganAlamat = null;

    public ?float $kurirLatitude = null;

    public ?float $kurirLongitude = null;

    public ?float $kurirAccuracy = null;

    public ?float $kurirSpeed = null;

    public ?float $kurirBearing = null;

    public ?string $kurirNama = null;

    public ?string $jenisKendaraan = null;

    public string $trackingStatus = 'searching';

    public function mount(int $id): void
    {
        // Load transaksi dengan relasi pelanggan, kurirJemput, dan kurirAntar
        $this->transaksi = Transaksi::query()
            ->with(['pelanggan', 'kurirJemput', 'kurirAntar'])
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
        }

        // Set data kurir (prioritas: kurirAntar > kurirJemput)
        $kurir = $this->transaksi->kurirAntar ?? $this->transaksi->kurirJemput;
        if ($kurir) {
            $this->kurirNama = $kurir->nama;
            $this->jenisKendaraan = $kurir->jenis_kendaraan;
        }

        // Get tracking data from cache
        $this->updateTrackingData();
    }

    public function updateTrackingData(): void
    {
        if (! $this->transaksi) {
            return;
        }

        // Get kurir ID (prioritas: kurirAntar > kurirJemput)
        $kurirId = $this->transaksi->kurir_antar_id ?? $this->transaksi->kurir_jemput_id;

        if (! $kurirId) {
            return;
        }

        $cacheKey = 'kurir_tracking_'.$kurirId;
        $trackingData = Cache::get($cacheKey);

        if ($trackingData) {
            // Update tracking data
            $this->kurirLatitude = $trackingData['latitude'] ?? null;
            $this->kurirLongitude = $trackingData['longitude'] ?? null;
            $this->kurirAccuracy = $trackingData['accuracy'] ?? null;
            $this->kurirSpeed = $trackingData['speed'] ?? null;
            $this->kurirBearing = $trackingData['bearing'] ?? null;

            $this->trackingStatus = 'active';

            // Dispatch to JavaScript
            $this->dispatch('tracking-data-updated', trackingData: $trackingData);
        } else {
            $this->trackingStatus = 'inactive';
        }
    }

    public function render(): View
    {
        return view('livewire.pelanggan.pages.pesanan.kurir-position');
    }
}

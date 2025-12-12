<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Component;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class LokasiSaya extends Component
{
    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?float $accuracy = null;

    public string $gpsStatus = 'initializing';

    public string $lastUpdate = '';

    public int $updateCount = 0;

    public function mount(): void
    {
        // Hanya load lokasi terakhir dari database untuk inisialisasi map
        // TIDAK menyimpan lokasi ke database, hanya untuk display
        $kurir = auth('kurir')->user();

        if ($kurir) {
            $this->latitude = $kurir->latitude ? (float) $kurir->latitude : null;
            $this->longitude = $kurir->longitude ? (float) $kurir->longitude : null;
            $this->lastUpdate = $kurir->updated_at?->locale('id')->diffForHumans() ?? 'Belum pernah update';
        }
    }

    public function updateLocation(float $latitude, float $longitude, float $accuracy): void
    {
        // Update property lokal untuk tracking
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracy = $accuracy;
        $this->gpsStatus = 'active';
        $this->updateCount++;
        $this->lastUpdate = now()->locale('id')->diffForHumans();

        // Stream koordinat terbaru ke UI secara real-time
        $this->stream(
            to: 'coordinates',
            content: $this->formatCoordinates(),
            replace: true,
        );

        // Stream status GPS
        $this->stream(
            to: 'gps-status',
            content: $this->getGpsStatusBadge(),
            replace: true,
        );

        // Stream akurasi
        $this->stream(
            to: 'accuracy-info',
            content: $this->formatAccuracy(),
            replace: true,
        );

        // Stream last update time
        $this->stream(
            to: 'last-update',
            content: $this->formatLastUpdate(),
            replace: true,
        );

        // Stream update counter untuk debugging (optional)
        $this->stream(
            to: 'update-counter',
            content: $this->formatUpdateCounter(),
            replace: true,
        );
    }

    public function updateGpsStatus(string $status): void
    {
        $this->gpsStatus = $status;

        $this->stream(
            to: 'gps-status',
            content: $this->getGpsStatusBadge(),
            replace: true,
        );
    }

    protected function formatCoordinates(): string
    {
        if (! $this->latitude || ! $this->longitude) {
            return '<span class="text-xs text-base-content/40">Menunggu GPS...</span>';
        }

        return sprintf(
            '<span class="text-xs font-mono text-base-content/80">%s, %s</span>',
            number_format($this->latitude, 6),
            number_format($this->longitude, 6)
        );
    }

    protected function formatAccuracy(): string
    {
        if (! $this->accuracy) {
            return '<span class="text-xs text-base-content/40">-</span>';
        }

        $color = match (true) {
            $this->accuracy <= 10 => 'text-success',
            $this->accuracy <= 50 => 'text-warning',
            default => 'text-error',
        };

        return sprintf(
            '<span class="text-xs %s font-medium">±%sm</span>',
            $color,
            number_format($this->accuracy, 1)
        );
    }

    protected function formatLastUpdate(): string
    {
        return sprintf(
            '<span class="text-xs text-base-content/40">%s</span>',
            $this->lastUpdate
        );
    }

    protected function formatUpdateCounter(): string
    {
        return sprintf(
            '<span class="text-xs text-base-content/40">Update: %d</span>',
            $this->updateCount
        );
    }

    protected function getGpsStatusBadge(): string
    {
        return match ($this->gpsStatus) {
            'active' => '<span class="badge badge-success badge-xs gap-1"><span class="w-1.5 h-1.5 bg-success-content rounded-full animate-pulse"></span>Aktif</span>',
            'searching' => '<span class="badge badge-warning badge-xs gap-1"><span class="w-1.5 h-1.5 bg-warning-content rounded-full animate-pulse"></span>Mencari...</span>',
            'error' => '<span class="badge badge-error badge-xs">Error</span>',
            'initializing' => '<span class="badge badge-ghost badge-xs">Inisialisasi...</span>',
            default => '<span class="badge badge-ghost badge-xs">Tidak aktif</span>',
        };
    }

    public function render(): View
    {
        return view('livewire.kurir.component.lokasi-saya');
    }
}

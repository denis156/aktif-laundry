<?php

declare(strict_types=1);

namespace App\Livewire\Management\Fonnte;

use App\Helper\FonnteHelper;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('WhatsApp Management')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast;

    public bool $loading = true;

    protected FonnteHelper $fonnte;

    public function boot(FonnteHelper $fonnte): void
    {
        $this->fonnte = $fonnte;
    }

    public function mount(): void
    {
        $this->loadDevices();
    }

    #[Computed]
    public function devices(): array
    {
        try {
            $this->loading = true;
            $response = $this->fonnte->getAllDevices();

            // Check if response is successful
            if (! $response['status']) {
                // API returned error
                $errorMessage = $response['error'] ?? 'Gagal memuat device';

                // Only show toast once on mount, not on every render
                if (! property_exists($this, 'errorShown')) {
                    $this->error($errorMessage, position: 'toast-bottom');
                }

                Log::error('Failed to load devices from Fonnte', [
                    'error' => $errorMessage,
                    'response' => $response,
                ]);

                return [];
            }

            // Check if data exists
            if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                return $response['data']['data'];
            }

            // No data found
            return [];
        } catch (Exception $e) {
            Log::error('Failed to load devices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        } finally {
            $this->loading = false;
        }
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Nama Device', 'sortable' => false],
            ['key' => 'device', 'label' => 'Nomor WhatsApp', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'sortable' => false],
            ['key' => 'expire', 'label' => 'Expired', 'sortable' => false],
            ['key' => 'messages_sent', 'label' => 'Messages Sent', 'sortable' => false],
            ['key' => 'quota', 'label' => 'Quota', 'sortable' => false],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ];
    }

    public function loadDevices(): void
    {
        // Clear cache untuk mendapatkan data terbaru
        $this->fonnte->clearDevicesCache();

        // Trigger computed property refresh
        unset($this->devices);

        $this->success('Data device berhasil diperbarui!', position: 'toast-bottom');
    }

    public function render()
    {
        return view('livewire.management.fonnte.index');
    }
}

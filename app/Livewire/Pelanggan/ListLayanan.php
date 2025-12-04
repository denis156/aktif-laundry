<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Models\Layanan;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Pilih Layanan')]
#[Layout('layouts.pelanggan.app')]
class ListLayanan extends Component
{
    use Toast;

    // Selected layanan IDs
    public array $selectedLayananIds = [];

    // Layanan list
    public array $layananList = [];

    public function mount(): void
    {
        $this->loadLayanan();

        // Load from session if coming back from form
        if (session()->has('selected_layanan_ids')) {
            $this->selectedLayananIds = session('selected_layanan_ids', []);
        }
    }

    public function loadLayanan(): void
    {
        try {
            $this->layananList = Layanan::where('status', 'Aktif')
                ->orderBy('is_popular', 'desc')
                ->orderBy('nama_layanan')
                ->get()
                ->map(function (Layanan $layanan) {
                    $price = $layanan->tipe_layanan === 'per_kg'
                        ? $layanan->harga_per_kg
                        : $layanan->harga_per_satuan;

                    $unit = $layanan->tipe_layanan === 'per_kg' ? 'kg' : ($layanan->satuan ?? 'pcs');

                    // Determine border and text color classes
                    if ($layanan->is_popular) {
                        $borderClass = 'border-warning';
                        $textClass = 'text-warning';
                        $badgeClass = 'badge-warning';
                        $btnClass = 'btn-warning';
                    } else {
                        $borderClass = 'border-success';
                        $textClass = 'text-success';
                        $badgeClass = 'badge-success';
                        $btnClass = 'btn-success';
                    }

                    return [
                        'id' => $layanan->id,
                        'nama' => $layanan->nama_layanan,
                        'tipe' => $layanan->tipe_layanan,
                        'harga' => $price,
                        'satuan' => $unit,
                        'durasi_jam' => $layanan->durasi_jam,
                        'deskripsi' => $layanan->deskripsi,
                        'is_popular' => $layanan->is_popular,
                        'icon' => $layanan->icon,
                        'border_class' => $borderClass,
                        'text_class' => $textClass,
                        'badge_class' => $badgeClass,
                        'btn_class' => $btnClass,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('ListLayanan: Failed to load layanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->layananList = [];
            $this->error('Gagal memuat daftar layanan', position: 'toast-top');
        }
    }

    public function isSelected(int $layananId): bool
    {
        return in_array($layananId, $this->selectedLayananIds, true);
    }

    public function toggleLayanan(int $layananId): void
    {
        if (in_array($layananId, $this->selectedLayananIds, true)) {
            // Remove dari array
            $this->selectedLayananIds = array_values(
                array_filter($this->selectedLayananIds, fn ($id) => $id !== $layananId)
            );
        } else {
            // Tambah ke array
            $this->selectedLayananIds[] = $layananId;
        }

        // Update session
        session(['selected_layanan_ids' => $this->selectedLayananIds]);

        Log::info('ListLayanan: Layanan toggled', [
            'layanan_id' => $layananId,
            'selected_ids' => $this->selectedLayananIds,
        ]);
    }

    public function lanjutKeForm(): void
    {
        if (empty($this->selectedLayananIds)) {
            $this->warning('Pilih minimal 1 layanan terlebih dahulu', position: 'toast-top');

            return;
        }

        // Store selected IDs in session
        session(['selected_layanan_ids' => $this->selectedLayananIds]);

        Log::info('ListLayanan: Proceeding to form', [
            'selected_ids' => $this->selectedLayananIds,
        ]);

        // Check if coming from edit pesanan (via session)
        if (session()->has('edit_transaksi_id')) {
            $transaksiId = session('edit_transaksi_id');

            // Clear the session BEFORE redirect
            session()->forget('edit_transaksi_id');

            Log::info('ListLayanan: Returning to edit pesanan', [
                'transaksi_id' => $transaksiId,
                'selected_ids' => $this->selectedLayananIds,
            ]);

            // Redirect back to edit pesanan
            $this->redirect(route('edit-pesanan.pelanggan', ['id' => $transaksiId]), navigate: true);
        } else {
            // Redirect to form
            $this->redirect(route('pesanan-form.pelanggan'), navigate: true);
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.list-layanan');
    }
}

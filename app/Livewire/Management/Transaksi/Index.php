<?php

declare(strict_types=1);

namespace App\Livewire\Management\Transaksi;

use App\Exports\TransaksiExport;
use App\Models\Transaksi;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

#[Title('Daftar Transaksi')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $deleteModal = false;

    public int $deleteId = 0;

    public string $deleteName = '';

    public array $sortBy = ['column' => 'kode_transaksi', 'direction' => 'desc'];

    public string $statusFilter = '';

    public string $metodePembayaranFilter = '';

    public string $tanggalMulai = '';

    public string $tanggalAkhir = '';

    public int $perPage = 10;

    public array $selected = [];

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter', 'metodePembayaranFilter', 'tanggalMulai', 'tanggalAkhir']);
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    public function confirmDelete($id, $kode): void
    {
        $this->deleteId = $id;
        $this->deleteName = $kode;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        try {
            $transaksi = Transaksi::findOrFail($this->deleteId);

            // Kurangi total transaksi pelanggan
            if ($transaksi->pelanggan) {
                $transaksi->pelanggan->decrement('total_transaksi');
            }

            $transaksi->delete();

            $this->success("Transaksi {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            Log::error('Transaksi Index: Failed to delete transaksi', [
                'transaksi_id' => $this->deleteId,
                'kode_transaksi' => $this->deleteName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus transaksi. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function exportExcel()
    {
        try {
            $filename = 'transaksi_'.now()->format('Y-m-d_His').'.xlsx';

            $this->info('Mengunduh '.count($this->selected).' transaksi...', position: 'toast-bottom');

            return Excel::download(new TransaksiExport($this->selected), $filename);
        } catch (Exception $e) {
            Log::error('Transaksi Index: Failed to export Excel', [
                'selected_count' => count($this->selected),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal export Excel. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_transaksi', 'label' => 'Kode', 'class' => 'w-20'],
            ['key' => 'kasir', 'label' => 'Kasir', 'class' => 'w-24', 'sortable' => false],
            ['key' => 'tanggal_masuk', 'label' => 'Tgl Masuk', 'class' => 'w-32'],
            ['key' => 'nama_pelanggan', 'label' => 'Pelanggan', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'layanan_info', 'label' => 'Layanan', 'class' => 'w-40', 'sortable' => false],
            ['key' => 'metadata_info', 'label' => 'Promo/Kurir', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'total', 'label' => 'Total', 'class' => 'w-28'],
            ['key' => 'metode_pembayaran', 'label' => 'Pembayaran', 'class' => 'w-24', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function transaksi()
    {
        return Transaksi::query()
            ->with(['pelanggan', 'kasir', 'transaksiLayanan.layanan'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('kode_transaksi', 'like', "%{$this->search}%")
                        ->orWhere('nama_pelanggan', 'like', "%{$this->search}%")
                        ->orWhereHas('transaksiLayanan', function ($subQuery) {
                            $subQuery->whereHas('layanan', function ($layananQuery) {
                                $layananQuery->where('nama_layanan', 'like', "%{$this->search}%");
                            });
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->metodePembayaranFilter, function ($query) {
                $query->where('metode_pembayaran', $this->metodePembayaranFilter);
            })
            ->when($this->tanggalMulai, function ($query) {
                $query->whereDate('tanggal_masuk', '>=', $this->tanggalMulai);
            })
            ->when($this->tanggalAkhir, function ($query) {
                $query->whereDate('tanggal_masuk', '<=', $this->tanggalAkhir);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.management.transaksi.index', [
            'transaksi' => $this->transaksi(),
            'headers' => $this->headers(),
        ]);
    }
}

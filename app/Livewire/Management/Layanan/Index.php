<?php

declare(strict_types=1);

namespace App\Livewire\Management\Layanan;

use App\Models\Layanan;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Layanan')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $deleteModal = false;

    public int $deleteId = 0;

    public string $deleteName = '';

    public array $sortBy = ['column' => 'kode_layanan', 'direction' => 'desc'];

    public string $statusFilter = '';

    public int $minHarga = 0;

    public int $maxHarga = 999999;

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter', 'minHarga', 'maxHarga']);
        $this->resetPage();
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    public function confirmDelete(int $id, string $nama): void
    {
        $this->deleteId = $id;
        $this->deleteName = $nama;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        try {
            $layanan = Layanan::findOrFail($this->deleteId);

            if ($this->cannotDeleteLayanan($layanan)) {
                return;
            }

            $layanan->delete();

            Log::info('Layanan deleted successfully', [
                'layanan_id' => $this->deleteId,
                'layanan_name' => $this->deleteName,
            ]);

            $this->success("Layanan {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->closeDeleteModal();
        } catch (ModelNotFoundException $e) {
            Log::error('Layanan not found for deletion', [
                'layanan_id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Layanan tidak ditemukan', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to delete layanan', [
                'layanan_id' => $this->deleteId,
                'layanan_name' => $this->deleteName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus layanan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    protected function cannotDeleteLayanan(Layanan $layanan): bool
    {
        $transaksiCount = $layanan->transaksiLayanan()->count();

        if ($transaksiCount > 0) {
            Log::warning('Attempt to delete layanan with existing transactions', [
                'layanan_id' => $this->deleteId,
                'layanan_name' => $this->deleteName,
                'transaksi_count' => $transaksiCount,
            ]);
            $this->error('Tidak dapat menghapus layanan yang sudah digunakan dalam transaksi', position: 'toast-bottom');

            return true;
        }

        return false;
    }

    protected function closeDeleteModal(): void
    {
        $this->deleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function headers(): array
    {
        return [
            ['key' => 'icon', 'label' => 'Icon', 'class' => 'w-12', 'sortable' => false],
            ['key' => 'kode_layanan', 'label' => 'Kode', 'class' => 'w-20'],
            ['key' => 'nama_layanan', 'label' => 'Nama Layanan', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'tipe_layanan', 'label' => 'Tipe', 'class' => 'w-20', 'sortable' => false],
            ['key' => 'harga', 'label' => 'Harga', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'durasi_jam', 'label' => 'Durasi (Jam)', 'class' => 'w-24'],
            ['key' => 'is_popular', 'label' => 'Popular', 'class' => 'w-20', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function layanan(): mixed
    {
        try {
            return $this->buildLayananQuery()->paginate($this->perPage);
        } catch (Exception $e) {
            Log::error('Failed to fetch layanan list', [
                'search' => $this->search,
                'statusFilter' => $this->statusFilter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal memuat data layanan', position: 'toast-bottom');

            return collect();
        }
    }

    protected function buildLayananQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Layanan::query()
            ->select([
                'id',
                'kode_layanan',
                'nama_layanan',
                'tipe_layanan',
                'satuan',
                'harga_per_kg',
                'harga_per_satuan',
                'durasi_jam',
                'status',
                'is_popular',
                'icon',
            ])
            ->when($this->search, fn ($query) => $this->applySearch($query))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->hasHargaFilter(), fn ($query) => $this->applyHargaFilter($query))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);
    }

    protected function applySearch(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where(function ($q) {
            $q->where('nama_layanan', 'like', "%{$this->search}%")
                ->orWhere('kode_layanan', 'like', "%{$this->search}%")
                ->orWhere('tipe_layanan', 'like', "%{$this->search}%");
        });
    }

    protected function hasHargaFilter(): bool
    {
        return $this->minHarga > 0 || $this->maxHarga < 999999;
    }

    protected function applyHargaFilter(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereBetween('harga_per_kg', [$this->minHarga, $this->maxHarga])
                ->orWhereBetween('harga_per_satuan', [$this->minHarga, $this->maxHarga]);
        });
    }

    public function render(): mixed
    {
        return view('livewire.management.layanan.index', [
            'layanan' => $this->layanan(),
            'headers' => $this->headers(),
        ]);
    }
}

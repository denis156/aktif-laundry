<?php

declare(strict_types=1);

namespace App\Livewire\Management\Promo;

use App\Models\Promo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Promo')]
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

    public array $sortBy = ['column' => 'tanggal_mulai', 'direction' => 'desc'];

    public string $statusFilter = '';

    public string $tipeDiskonFilter = '';

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter', 'tipeDiskonFilter']);
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
            $promo = Promo::findOrFail($this->deleteId);

            // Check if promo is being used in any transactions
            $usageCount = $promo->kuota_terpakai ?? 0;
            if ($usageCount > 0) {
                Log::warning('Attempt to delete promo with existing usage', [
                    'promo_id' => $this->deleteId,
                    'promo_name' => $this->deleteName,
                    'usage_count' => $usageCount,
                ]);
                $this->error('Tidak dapat menghapus promo yang sudah digunakan dalam transaksi', position: 'toast-bottom');

                return;
            }

            $promo->delete();

            Log::info('Promo deleted successfully', [
                'promo_id' => $this->deleteId,
                'promo_name' => $this->deleteName,
            ]);

            $this->success("Promo {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (ModelNotFoundException $e) {
            Log::error('Promo not found for deletion', [
                'promo_id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Promo tidak ditemukan', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to delete promo', [
                'promo_id' => $this->deleteId,
                'promo_name' => $this->deleteName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus promo. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'banner', 'label' => 'Banner', 'class' => 'w-20', 'sortable' => false],
            ['key' => 'kode_promo', 'label' => 'Kode', 'class' => 'w-28'],
            ['key' => 'nama_promo', 'label' => 'Nama Promo', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'tipe_diskon', 'label' => 'Tipe', 'class' => 'w-24', 'sortable' => false],
            ['key' => 'nilai_diskon', 'label' => 'Diskon', 'class' => 'w-28', 'sortable' => false],
            ['key' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'class' => 'w-32'],
            ['key' => 'tanggal_berakhir', 'label' => 'Tanggal Selesai', 'class' => 'w-32'],
            ['key' => 'kuota', 'label' => 'Kuota', 'class' => 'w-24', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function promo(): LengthAwarePaginator
    {
        return Promo::query()
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('kode_promo', 'like', "%{$this->search}%")
                        ->orWhere('nama_promo', 'like', "%{$this->search}%")
                        ->orWhere('deskripsi', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->tipeDiskonFilter, function (Builder $query): void {
                $query->where('tipe_diskon', $this->tipeDiskonFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render(): mixed
    {
        return view('livewire.management.promo.index', [
            'promo' => $this->promo(),
            'headers' => $this->headers(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Management\JenisPakaian;

use App\Models\JenisPakaian;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Jenis Pakaian')]
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

    public array $sortBy = ['column' => 'kode_jenis', 'direction' => 'desc'];

    public string $statusFilter = '';

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter']);
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
            $jenisPakaian = JenisPakaian::findOrFail($this->deleteId);

            Log::info('Deleting jenis pakaian', [
                'id' => $jenisPakaian->id,
                'kode_jenis' => $jenisPakaian->kode_jenis,
                'nama_jenis' => $jenisPakaian->nama_jenis,
            ]);

            $jenisPakaian->delete();

            $this->success("Jenis Pakaian {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (QueryException $e) {
            Log::error('Database error deleting jenis pakaian', [
                'id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);

            if ($e->errorInfo[1] == 1451) {
                $this->error('Jenis Pakaian tidak dapat dihapus karena masih digunakan.', position: 'toast-bottom');
            } else {
                $this->error('Gagal menghapus jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
            }
        } catch (Exception $e) {
            Log::error('Error deleting jenis pakaian', [
                'id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);

            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'icon', 'label' => 'Icon', 'class' => 'w-12', 'sortable' => false],
            ['key' => 'kode_jenis', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama_jenis', 'label' => 'Nama Jenis', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'keterangan', 'label' => 'Keterangan', 'sortable' => false],
            ['key' => 'penanganan_khusus', 'label' => 'Penanganan Khusus', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function jenisPakaian(): LengthAwarePaginator
    {
        try {
            return JenisPakaian::query()
                ->when($this->search, function ($query): void {
                    $query->where(function ($q): void {
                        $q->where('nama_jenis', 'ilike', "%{$this->search}%")
                            ->orWhere('keterangan', 'ilike', "%{$this->search}%")
                            ->orWhere('kode_jenis', 'ilike', "%{$this->search}%");
                    });
                })
                ->when($this->statusFilter, function ($query): void {
                    $query->where('status', $this->statusFilter);
                })
                ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
                ->paginate($this->perPage);
        } catch (Exception $e) {
            Log::error('Error fetching jenis pakaian list', [
                'search' => $this->search,
                'status_filter' => $this->statusFilter,
                'error' => $e->getMessage(),
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage
            );
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.jenis-pakaian.index', [
            'jenisPakaian' => $this->jenisPakaian(),
            'headers' => $this->headers(),
        ]);
    }
}

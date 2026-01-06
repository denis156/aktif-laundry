<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Kurir;

use App\Helper\Database\KurirHelper;
use App\Models\Kurir;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Kurir')]
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

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public string $statusFilter = '';

    public string $jenisKendaraanFilter = '';

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter', 'jenisKendaraanFilter']);
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    public function confirmDelete($id, $nama): void
    {
        $this->deleteId = $id;
        $this->deleteName = $nama;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        try {
            $kurir = Kurir::findOrFail($this->deleteId);

            Log::info('Kurir deleted', [
                'kurir_id' => $kurir->id,
                'kode_kurir' => $kurir->kode_kurir,
                'nama' => $kurir->nama,
                'deleted_by' => Auth::id(),
            ]);

            $kurir->delete();

            $this->success("Kurir {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            Log::error('Failed to delete kurir', [
                'kurir_id' => $this->deleteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus kurir. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'avatar', 'label' => 'Avatar', 'class' => 'w-16', 'sortable' => false],
            ['key' => 'kode_kurir', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama', 'label' => 'Nama Kurir', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'no_hp', 'label' => 'No. HP', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'alamat', 'label' => 'Alamat', 'class' => 'w-64', 'sortable' => false],
            ['key' => 'jenis_kendaraan', 'label' => 'Kendaraan', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function kurir(): LengthAwarePaginator
    {
        return Kurir::query()
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('nama', 'like', "%{$this->search}%")
                        ->orWhere('kode_kurir', 'like', "%{$this->search}%")
                        ->orWhere('no_hp', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('no_kendaraan', 'like', "%{$this->search}%")
                        ->orWhere('jenis_kendaraan', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->jenisKendaraanFilter, function (Builder $query): void {
                $query->where('jenis_kendaraan', $this->jenisKendaraanFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.kurir.index', [
            'kurir' => $this->kurir(),
            'headers' => $this->headers(),
            'statusOptions' => KurirHelper::getStatusOptions(),
            'jenisKendaraanOptions' => KurirHelper::getJenisKendaraanOptions(),
        ]);
    }
}

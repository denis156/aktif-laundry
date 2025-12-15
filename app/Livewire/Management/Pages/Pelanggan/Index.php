<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Pelanggan;

use App\Helper\PhoneNumber;
use App\Models\Pelanggan;
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

#[Title('Daftar Pelanggan')]
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

    public array $sortBy = ['column' => 'kode_pelanggan', 'direction' => 'desc'];

    public string $statusFilter = '';

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter']);
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
            $pelanggan = Pelanggan::findOrFail($this->deleteId);

            Log::info('Pelanggan deleted', [
                'pelanggan_id' => $pelanggan->id,
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'nama' => $pelanggan->nama,
                'deleted_by' => Auth::id(),
            ]);

            $pelanggan->delete();

            $this->success("Pelanggan {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            Log::error('Failed to delete pelanggan', [
                'pelanggan_id' => $this->deleteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'avatar', 'label' => 'Avatar', 'class' => 'w-16', 'sortable' => false],
            ['key' => 'kode_pelanggan', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama', 'label' => 'Nama Pelanggan', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'no_hp', 'label' => 'No. HP', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'tanggal_daftar', 'label' => 'Tanggal Daftar', 'class' => 'w-32'],
            ['key' => 'transaksi_count', 'label' => 'Total Transaksi', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function pelanggan(): LengthAwarePaginator
    {
        return Pelanggan::query()
            ->withCount('transaksi')
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('nama', 'like', "%{$this->search}%")
                        ->orWhere('kode_pelanggan', 'like', "%{$this->search}%")
                        ->orWhere('no_hp', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('alamat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function statusOptions(): array
    {
        return [
            ['id' => '', 'name' => 'Semua Status'],
            ['id' => 'Aktif', 'name' => 'Aktif'],
            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
        ];
    }

    public function render(): mixed
    {
        $pelanggan = $this->pelanggan();

        // Format nomor HP untuk display
        $pelanggan->getCollection()->transform(function ($item) {
            $item->no_hp_display = PhoneNumber::formatLocal($item->no_hp) ?? $item->no_hp;

            return $item;
        });

        return view('livewire.management.pages.pelanggan.index', [
            'pelanggan' => $pelanggan,
            'headers' => $this->headers(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }
}

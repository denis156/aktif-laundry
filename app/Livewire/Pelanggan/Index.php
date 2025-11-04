<?php

namespace App\Livewire\Pelanggan;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Pelanggan;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Daftar Pelanggan')]
class Index extends Component
{
    use Toast, WithPagination;

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
            $pelanggan->delete();

            $this->success("Pelanggan {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            $this->error('Gagal menghapus pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_pelanggan', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama', 'label' => 'Nama Pelanggan', 'class' => 'w-48'],
            ['key' => 'no_hp', 'label' => 'No. HP', 'class' => 'w-32'],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-48'],
            ['key' => 'tanggal_daftar', 'label' => 'Tanggal Daftar', 'class' => 'w-32'],
            ['key' => 'total_transaksi', 'label' => 'Total Transaksi', 'class' => 'w-32'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24'],
        ];
    }

    public function pelanggan()
    {
        return Pelanggan::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'like', "%{$this->search}%")
                      ->orWhere('kode_pelanggan', 'like', "%{$this->search}%")
                      ->orWhere('no_hp', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('alamat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pelanggan.index', [
            'pelanggan' => $this->pelanggan(),
            'headers' => $this->headers()
        ]);
    }
}

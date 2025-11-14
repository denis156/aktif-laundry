<?php

namespace App\Livewire\Layanan;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Daftar Layanan')]
class Index extends Component
{
    use Toast, WithPagination;

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
            $layanan = Layanan::findOrFail($this->deleteId);
            $layanan->delete();

            $this->success("Layanan {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            $this->error('Gagal menghapus layanan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_layanan', 'label' => 'Kode', 'class' => 'w-20'],
            ['key' => 'nama_layanan', 'label' => 'Nama Layanan', 'class' => 'w-48', 'sortable' => false],
            ['key' => 'tipe_layanan', 'label' => 'Tipe', 'class' => 'w-20', 'sortable' => false],
            ['key' => 'harga', 'label' => 'Harga', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'durasi_jam', 'label' => 'Durasi (Jam)', 'class' => 'w-24'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function layanan()
    {
        return Layanan::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_layanan', 'like', "%{$this->search}%")
                      ->orWhere('kode_layanan', 'like', "%{$this->search}%")
                      ->orWhere('tipe_layanan', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->minHarga > 0 || $this->maxHarga < 999999, function ($query) {
                $query->where(function ($q) {
                    $q->whereBetween('harga_per_kg', [$this->minHarga, $this->maxHarga])
                      ->orWhereBetween('harga_per_satuan', [$this->minHarga, $this->maxHarga]);
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.layanan.index', [
            'layanan' => $this->layanan(),
            'headers' => $this->headers()
        ]);
    }
}

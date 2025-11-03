<?php

namespace App\Livewire\Layanan;

use App\Models\Layanan;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Mary\Traits\Toast;

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
        } catch (\Exception $e) {
            $this->error('Gagal menghapus layanan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_layanan', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama_layanan', 'label' => 'Nama Layanan', 'class' => 'w-48'],
            ['key' => 'harga_per_kg', 'label' => 'Harga/Kg', 'class' => 'w-32'],
            ['key' => 'durasi_jam', 'label' => 'Durasi (Jam)', 'class' => 'w-32'],
            ['key' => 'deskripsi', 'label' => 'Deskripsi'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24'],
        ];
    }

    public function layanan()
    {
        return Layanan::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_layanan', 'like', "%{$this->search}%")
                      ->orWhere('deskripsi', 'like', "%{$this->search}%")
                      ->orWhere('kode_layanan', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->whereBetween('harga_per_kg', [$this->minHarga, $this->maxHarga])
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

<?php

namespace App\Livewire\JenisPakaian;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\JenisPakaian;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Daftar Jenis Pakaian')]
class Index extends Component
{
    use Toast, WithPagination;

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

    public function confirmDelete($id, $nama): void
    {
        $this->deleteId = $id;
        $this->deleteName = $nama;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        try {
            $jenisPakaian = JenisPakaian::findOrFail($this->deleteId);
            $jenisPakaian->delete();

            $this->success("Jenis Pakaian {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (Exception $e) {
            $this->error('Gagal menghapus jenis pakaian: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_jenis', 'label' => 'Kode', 'class' => 'w-24'],
            ['key' => 'nama_jenis', 'label' => 'Nama Jenis', 'class' => 'w-48'],
            ['key' => 'keterangan', 'label' => 'Keterangan'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24'],
        ];
    }

    public function jenisPakaian()
    {
        return JenisPakaian::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_jenis', 'like', "%{$this->search}%")
                      ->orWhere('keterangan', 'like', "%{$this->search}%")
                      ->orWhere('kode_jenis', 'like', "%{$this->search}%");
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
        return view('livewire.jenis-pakaian.index', [
            'jenisPakaian' => $this->jenisPakaian(),
            'headers' => $this->headers()
        ]);
    }
}

<?php

namespace App\Livewire\Layanan;

use App\Models\Layanan;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Edit Layanan')]
class Edit extends Component
{
    use Toast;

    public int $layananId;

    public array $formData = [
        'kode_layanan' => '',
        'nama_layanan' => '',
        'harga_per_kg' => '',
        'durasi_jam' => '',
        'deskripsi' => '',
        'status' => 'Aktif',
    ];

    public function mount($id)
    {
        $this->layananId = $id;
        $this->loadLayanan();
    }

    protected function loadLayanan()
    {
        try {
            $layanan = Layanan::findOrFail($this->layananId);

            $this->formData = [
                'kode_layanan' => $layanan->kode_layanan,
                'nama_layanan' => $layanan->nama_layanan,
                'harga_per_kg' => $layanan->harga_per_kg,
                'durasi_jam' => $layanan->durasi_jam,
                'deskripsi' => $layanan->deskripsi,
                'status' => $layanan->status,
            ];
        } catch (\Exception $e) {
            $this->error('Layanan tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/admin/layanan', navigate: true);
        }
    }

    public function save()
    {
        $this->validate([
            'formData.nama_layanan' => 'required|string|max:255',
            'formData.harga_per_kg' => 'required|numeric|min:0',
            'formData.durasi_jam' => 'required|integer|min:1',
            'formData.deskripsi' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            $layanan = Layanan::findOrFail($this->layananId);
            $layanan->update($this->formData);

            $this->success('Layanan berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/admin/layanan', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan layanan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.layanan.edit');
    }
}

<?php

namespace App\Livewire\JenisPakaian;

use App\Models\JenisPakaian;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Edit Jenis Pakaian')]
class Edit extends Component
{
    use Toast;

    public int $jenisPakaianId;

    public array $formData = [
        'kode_jenis' => '',
        'nama_jenis' => '',
        'keterangan' => '',
        'status' => 'Aktif',
    ];

    public function mount($id)
    {
        $this->jenisPakaianId = $id;
        $this->loadJenisPakaian();
    }

    protected function loadJenisPakaian()
    {
        try {
            $jenisPakaian = JenisPakaian::findOrFail($this->jenisPakaianId);

            $this->formData = [
                'kode_jenis' => $jenisPakaian->kode_jenis,
                'nama_jenis' => $jenisPakaian->nama_jenis,
                'keterangan' => $jenisPakaian->keterangan,
                'status' => $jenisPakaian->status,
            ];
        } catch (\Exception $e) {
            $this->error('Jenis Pakaian tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/jenis-pakaian', navigate: true);
        }
    }

    public function save()
    {
        $this->validate([
            'formData.nama_jenis' => 'required|string|max:255',
            'formData.keterangan' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            $jenisPakaian = JenisPakaian::findOrFail($this->jenisPakaianId);
            $jenisPakaian->update($this->formData);

            $this->success('Jenis Pakaian berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/jenis-pakaian', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan jenis pakaian: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.jenis-pakaian.edit');
    }
}

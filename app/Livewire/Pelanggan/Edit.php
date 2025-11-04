<?php

namespace App\Livewire\Pelanggan;

use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\Pelanggan;
use Livewire\Attributes\Title;

#[Title('Edit Pelanggan')]
class Edit extends Component
{
    use Toast;

    public int $pelangganId;

    public array $formData = [
        'kode_pelanggan' => '',
        'nama' => '',
        'no_hp' => '',
        'alamat' => '',
        'email' => '',
        'tanggal_daftar' => '',
        'status' => 'Aktif',
    ];

    public function mount($id)
    {
        $this->pelangganId = $id;
        $this->loadPelanggan();
    }

    protected function loadPelanggan()
    {
        try {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);

            $this->formData = [
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'nama' => $pelanggan->nama,
                'no_hp' => $pelanggan->no_hp,
                'alamat' => $pelanggan->alamat,
                'email' => $pelanggan->email,
                'tanggal_daftar' => $pelanggan->tanggal_daftar->format('Y-m-d H:i'),
                'status' => $pelanggan->status,
            ];
        } catch (\Exception $e) {
            $this->error('Pelanggan tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/admin/pelanggan', navigate: true);
        }
    }

    public function save()
    {
        $this->validate([
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15',
            'formData.alamat' => 'required|string',
            'formData.email' => 'nullable|email|max:255',
            'formData.tanggal_daftar' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);

            // Konversi format tanggal
            $data = $this->formData;
            $data['tanggal_daftar'] = Carbon::parse($this->formData['tanggal_daftar'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s');

            $pelanggan->update($data);

            $this->success('Pelanggan berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/admin/pelanggan', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.pelanggan.edit');
    }
}

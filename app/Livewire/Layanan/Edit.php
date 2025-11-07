<?php

namespace App\Livewire\Layanan;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;

#[Title('Edit Layanan')]
class Edit extends Component
{
    use Toast;

    public int $layananId;

    public array $formData = [
        'kode_layanan' => '',
        'nama_layanan' => '',
        'tipe_layanan' => 'per_kg',
        'harga_per_kg' => '',
        'harga_per_satuan' => '',
        'satuan' => '',
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
                'tipe_layanan' => $layanan->tipe_layanan,
                'harga_per_kg' => $layanan->harga_per_kg,
                'harga_per_satuan' => $layanan->harga_per_satuan,
                'satuan' => $layanan->satuan,
                'durasi_jam' => $layanan->durasi_jam,
                'deskripsi' => $layanan->deskripsi,
                'status' => $layanan->status,
            ];
        } catch (Exception $e) {
            $this->error('Layanan tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/admin/layanan', navigate: true);
        }
    }

    public function save()
    {
        // Build validation rules based on tipe layanan
        $rules = [
            'formData.nama_layanan' => 'required|string|max:255',
            'formData.tipe_layanan' => 'required|in:per_kg,per_satuan',
            'formData.durasi_jam' => 'required|integer|min:1',
            'formData.deskripsi' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ];

        // Add conditional validation based on tipe layanan
        if ($this->formData['tipe_layanan'] === 'per_kg') {
            $rules['formData.harga_per_kg'] = 'required|numeric|min:0';
            $rules['formData.harga_per_satuan'] = 'nullable|numeric|min:0';
            $rules['formData.satuan'] = 'nullable|string|max:10';
        } else {
            $rules['formData.harga_per_kg'] = 'nullable|numeric|min:0';
            $rules['formData.harga_per_satuan'] = 'required|numeric|min:0';
            $rules['formData.satuan'] = 'required|string|max:10';
        }

        $this->validate($rules);

        try {
            // Prepare data before saving
            $saveData = $this->formData;

            // Set default values for fields based on tipe layanan
            if ($saveData['tipe_layanan'] === 'per_kg') {
                $saveData['harga_per_satuan'] = null;
                $saveData['satuan'] = 'kg';
            } else {
                $saveData['harga_per_kg'] = 0;
            }

            $layanan = Layanan::findOrFail($this->layananId);
            $layanan->update($saveData);

            $this->success('Layanan berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/admin/layanan', navigate: true);
        } catch (Exception $e) {
            Log::error('Error updating layanan: ' . $e->getMessage());
            $this->error('Gagal memperbarui layanan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.layanan.edit');
    }
}

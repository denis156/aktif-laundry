<?php

namespace App\Livewire\Pelanggan;

use App\Models\Pelanggan;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Tambah Pelanggan')]
class Create extends Component
{
    use Toast;

    public array $formData = [
        'kode_pelanggan' => '',
        'nama' => '',
        'no_hp' => '',
        'alamat' => '',
        'email' => '',
        'tanggal_daftar' => '',
        'status' => 'Aktif',
    ];

    public function mount()
    {
        $this->formData['kode_pelanggan'] = $this->generateKode();
        $this->formData['tanggal_daftar'] = now()->format('Y-m-d');
    }

    protected function generateKode(): string
    {
        // Get prefix from Setting, default 'PLG'
        $prefix = Setting::get('format_id_pelanggan', 'PLG');
        $prefixLength = strlen($prefix);

        // Get latest kode from database
        $lastPelanggan = Pelanggan::orderBy('kode_pelanggan', 'desc')->first();

        if (!$lastPelanggan) {
            return $prefix . '001';
        }

        // Extract number part after prefix
        $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, $prefixLength);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate([
            'formData.kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan',
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15',
            'formData.alamat' => 'required|string',
            'formData.email' => 'nullable|email|max:255',
            'formData.tanggal_daftar' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            Pelanggan::create($this->formData);

            $this->success('Pelanggan berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/pelanggan', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.pelanggan.create');
    }
}

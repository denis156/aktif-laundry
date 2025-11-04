<?php

namespace App\Livewire\JenisPakaian;

use App\Models\JenisPakaian;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Tambah Jenis Pakaian')]
class Create extends Component
{
    use Toast;

    public array $formData = [
        'kode_jenis' => '',
        'nama_jenis' => '',
        'keterangan' => '',
        'status' => 'Aktif',
    ];

    public function mount()
    {
        $this->refreshKodeJenisPakaian();
    }

    public function refreshKodeJenisPakaian()
    {
        $this->formData['kode_jenis'] = $this->generateKode();
    }

    protected function generateKode(): string
    {
        // Get prefix from Setting, default 'JNS'
        $prefix = Setting::get('format_id_jenis_pakaian', 'JNS');
        $prefixLength = strlen($prefix);

        // Get latest kode from database, including soft-deleted records to handle gaps
        $lastJenisPakaian = JenisPakaian::withTrashed()->orderBy('kode_jenis', 'desc')->first();

        if (!$lastJenisPakaian) {
            return $prefix . '001';
        }

        // Extract number part after prefix
        $lastNumber = (int) substr($lastJenisPakaian->kode_jenis, $prefixLength);
        
        // Check if there are any gaps in the numbering by finding the next available number
        $nextNumber = $lastNumber + 1;
        
        // Verify if this number is already used (in case of deletions)
        while (JenisPakaian::where('kode_jenis', $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate([
            'formData.kode_jenis' => 'required|unique:jenis_pakaian,kode_jenis',
            'formData.nama_jenis' => 'required|string|max:255',
            'formData.keterangan' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            JenisPakaian::create($this->formData);

            $this->success('Jenis Pakaian berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/admin/jenis-pakaian', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan jenis pakaian: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.jenis-pakaian.create');
    }
}

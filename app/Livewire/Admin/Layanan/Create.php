<?php

namespace App\Livewire\Admin\Layanan;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

#[Title('Tambah Layanan')]

#[Layout('layouts.admin.app')]
class Create extends Component
{
    use Toast;

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

    public function mount()
    {
        $this->refreshKodeLayanan();
    }

    public function refreshKodeLayanan()
    {
        $this->formData['kode_layanan'] = $this->generateKode();
    }

    protected function generateKode(): string
    {
        // Get prefix from Setting, default 'LYN'
        $prefix = Setting::get('format_id_layanan', 'LYN');
        $prefixLength = strlen($prefix);

        // Get latest kode from database, including soft-deleted records to handle gaps
        $lastLayanan = Layanan::withTrashed()->orderBy('kode_layanan', 'desc')->first();

        if (!$lastLayanan) {
            return $prefix . '001';
        }

        // Extract number part after prefix
        $lastNumber = (int) substr($lastLayanan->kode_layanan, $prefixLength);

        // Check if there are any gaps in the numbering by finding the next available number
        $nextNumber = $lastNumber + 1;

        // Verify if this number is already used (in case of deletions)
        while (Layanan::where('kode_layanan', $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        // Build validation rules based on tipe layanan
        $rules = [
            'formData.kode_layanan' => 'required|unique:layanan,kode_layanan',
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

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() use ($saveData) {
                // Cek ulang apakah kode layanan sudah ada, jika ya generate ulang
                if (Layanan::where('kode_layanan', $saveData['kode_layanan'])->exists()) {
                    $this->refreshKodeLayanan();
                    $saveData['kode_layanan'] = $this->formData['kode_layanan'];
                }

                Layanan::create($saveData);
            });

            $this->success('Layanan berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/admin/layanan', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeLayanan();
                $this->success('Kode layanan di-regenerate, silakan coba lagi', position: 'toast-bottom');
                return;
            }
            Log::error('Database error saving layanan: ' . $e->getMessage());
            $this->error('Gagal menyimpan layanan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('General error saving layanan: ' . $e->getMessage());
            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.layanan.create');
    }
}

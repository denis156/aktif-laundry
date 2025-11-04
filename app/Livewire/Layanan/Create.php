<?php

namespace App\Livewire\Layanan;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

#[Title('Tambah Layanan')]
class Create extends Component
{
    use Toast;

    public array $formData = [
        'kode_layanan' => '',
        'nama_layanan' => '',
        'harga_per_kg' => '',
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
        $this->validate([
            'formData.kode_layanan' => 'required|unique:layanan,kode_layanan',
            'formData.nama_layanan' => 'required|string|max:255',
            'formData.harga_per_kg' => 'required|numeric|min:0',
            'formData.durasi_jam' => 'required|integer|min:1',
            'formData.deskripsi' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
                // Cek ulang apakah kode layanan sudah ada, jika ya generate ulang
                if (Layanan::where('kode_layanan', $this->formData['kode_layanan'])->exists()) {
                    $this->refreshKodeLayanan();
                }

                Layanan::create($this->formData);
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
            $this->error('Gagal menyimpan layanan: ' . $e->getMessage(), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menyimpan layanan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.layanan.create');
    }
}

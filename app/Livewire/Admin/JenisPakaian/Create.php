<?php

namespace App\Livewire\Admin\JenisPakaian;

use Exception;
use Mary\Traits\Toast;
use App\Models\Setting;
use Livewire\Component;
use App\Models\JenisPakaian;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

#[Title('Tambah Jenis Pakaian')]

#[Layout('layouts.admin.app')]
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
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
                // Cek ulang apakah kode jenis pakaian sudah ada, jika ya generate ulang
                if (JenisPakaian::where('kode_jenis', $this->formData['kode_jenis'])->exists()) {
                    $this->refreshKodeJenisPakaian();
                }

                JenisPakaian::create($this->formData);
            });

            $this->success('Jenis Pakaian berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/admin/jenis-pakaian', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeJenisPakaian();
                $this->success('Kode jenis pakaian di-regenerate, silakan coba lagi', position: 'toast-bottom');
                return;
            }
            \Log::error('Database error saving jenis pakaian: ' . $e->getMessage());
            $this->error('Gagal menyimpan jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            \Log::error('General error saving jenis pakaian: ' . $e->getMessage());
            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.jenis-pakaian.create');
    }
}

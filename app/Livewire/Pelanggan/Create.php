<?php

namespace App\Livewire\Pelanggan;

use Exception;
use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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
        $this->refreshKodePelanggan();
        $this->formData['tanggal_daftar'] = now()->format('Y-m-d H:i');
    }

    public function refreshKodePelanggan()
    {
        $this->formData['kode_pelanggan'] = $this->generateKode();
    }

    protected function generateKode(): string
    {
        // Get prefix from Setting, default 'PLG'
        $prefix = Setting::get('format_id_pelanggan', 'PLG');
        $prefixLength = strlen($prefix);

        // Get latest kode from database, including soft-deleted records to handle gaps
        $lastPelanggan = Pelanggan::withTrashed()->orderBy('kode_pelanggan', 'desc')->first();

        if (!$lastPelanggan) {
            return $prefix . '001';
        }

        // Extract number part after prefix
        $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, $prefixLength);

        // Check if there are any gaps in the numbering by finding the next available number
        $nextNumber = $lastNumber + 1;

        // Verify if this number is already used (in case of deletions)
        while (Pelanggan::where('kode_pelanggan', $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
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
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
                // Cek ulang apakah kode pelanggan sudah ada, jika ya generate ulang
                if (Pelanggan::where('kode_pelanggan', $this->formData['kode_pelanggan'])->exists()) {
                    $this->refreshKodePelanggan();
                }

                // Konversi format tanggal
                $data = $this->formData;
                $data['tanggal_daftar'] = Carbon::parse($this->formData['tanggal_daftar'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s');

                Pelanggan::create($data);
            });

            $this->success('Pelanggan berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/admin/pelanggan', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodePelanggan();
                $this->success('Kode pelanggan di-regenerate, silakan coba lagi', position: 'toast-bottom');
                return;
            }
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.pelanggan.create');
    }
}

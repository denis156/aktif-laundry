<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pelanggan;

use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Tambah Pelanggan')]
#[Layout('layouts.management.app')]
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

    public function mount(): void
    {
        $this->refreshKodePelanggan();
        $this->formData['tanggal_daftar'] = now()->format('Y-m-d H:i');
    }

    public function refreshKodePelanggan(): void
    {
        try {
            $this->formData['kode_pelanggan'] = PelangganHelper::generateKodePelanggan();
        } catch (Exception $e) {
            Log::error('Failed to generate kode pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function save(): void
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
            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () use ($normalizedPhone) {
                // Cek ulang apakah kode pelanggan sudah ada, jika ya generate ulang
                if (Pelanggan::where('kode_pelanggan', $this->formData['kode_pelanggan'])->exists()) {
                    $this->refreshKodePelanggan();
                }

                // Konversi format tanggal
                $data = $this->formData;
                $data['tanggal_daftar'] = Carbon::parse($this->formData['tanggal_daftar'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s');
                $data['no_hp'] = $normalizedPhone;
                $data['total_transaksi'] = 0;

                Pelanggan::create($data);
            });

            $this->success('Pelanggan berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                Log::warning('Duplicate entry detected when creating pelanggan', [
                    'kode_pelanggan' => $this->formData['kode_pelanggan'],
                    'error' => $e->getMessage(),
                ]);
                $this->refreshKodePelanggan();
                $this->warning('Kode pelanggan di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            Log::error('Database error when creating pelanggan', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to create pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.pelanggan.create');
    }
}

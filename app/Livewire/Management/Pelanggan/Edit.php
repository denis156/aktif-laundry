<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pelanggan;

use App\Helper\PhoneNumber;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Edit Pelanggan')]
#[Layout('layouts.management.app')]
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

    public function mount($id): void
    {
        $this->pelangganId = (int) $id;
        $this->loadPelanggan();
    }

    protected function loadPelanggan(): void
    {
        try {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);

            $this->formData = [
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'nama' => $pelanggan->nama,
                'no_hp' => PhoneNumber::formatLocal($pelanggan->no_hp) ?? $pelanggan->no_hp,
                'alamat' => $pelanggan->alamat,
                'email' => $pelanggan->email ?? '',
                'tanggal_daftar' => $pelanggan->tanggal_daftar->format('Y-m-d H:i'),
                'status' => $pelanggan->status,
            ];
        } catch (Exception $e) {
            Log::error('Failed to load pelanggan for edit', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Pelanggan tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        }
    }

    public function save(): void
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

            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Konversi format tanggal
            $data = $this->formData;
            $data['tanggal_daftar'] = Carbon::parse($this->formData['tanggal_daftar'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s');
            $data['no_hp'] = $normalizedPhone;

            $pelanggan->update($data);

            $this->success('Pelanggan berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        } catch (QueryException $e) {
            Log::error('Database error when updating pelanggan', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to update pelanggan', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.pelanggan.edit');
    }
}

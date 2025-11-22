<?php

declare(strict_types=1);

namespace App\Livewire\Management\Promo;

use Exception;
use App\Models\Promo;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

#[Title('Tambah Promo')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

    public array $formData = [
        'kode_promo' => '',
        'nama_promo' => '',
        'deskripsi' => '',
        'tipe_diskon' => 'persen',
        'nilai_diskon' => 0,
        'diskon_maksimal' => null,
        'min_transaksi' => null,
        'tanggal_mulai' => '',
        'tanggal_berakhir' => '',
        'kuota_total' => null,
        'max_per_user' => null,
        'berlaku_untuk' => 'semua',
        'status' => 'Aktif',
    ];

    public function mount(): void
    {
        $this->formData['tanggal_mulai'] = now()->format('Y-m-d');
        $this->formData['tanggal_berakhir'] = now()->addDays(30)->format('Y-m-d');
    }

    public function updatedFormDataTipeDiskon(mixed $value): void
    {
        // Reset diskon maksimal jika tipe nominal
        if ($value === 'nominal') {
            $this->formData['diskon_maksimal'] = null;
        }
    }

    public function save(): void
    {
        // Validasi
        $rules = [
            'formData.kode_promo' => 'required|string|max:50|unique:promo,kode_promo',
            'formData.nama_promo' => 'required|string|max:255',
            'formData.deskripsi' => 'nullable|string',
            'formData.tipe_diskon' => 'required|in:persen,nominal',
            'formData.nilai_diskon' => 'required|integer|min:1',
            'formData.diskon_maksimal' => 'nullable|integer|min:1',
            'formData.min_transaksi' => 'nullable|integer|min:1',
            'formData.tanggal_mulai' => 'required|date',
            'formData.tanggal_berakhir' => 'required|date|after_or_equal:formData.tanggal_mulai',
            'formData.kuota_total' => 'nullable|integer|min:1',
            'formData.max_per_user' => 'nullable|integer|min:1',
            'formData.berlaku_untuk' => 'required|in:semua,layanan_tertentu,pelanggan_baru',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ];

        $this->validate($rules);

        try {
            DB::transaction(function () {
                $promoData = $this->formData;
                $promoData['kuota_terpakai'] = 0;

                // Convert null string to actual null
                $promoData['diskon_maksimal'] = $this->formData['diskon_maksimal'] ?: null;
                $promoData['min_transaksi'] = $this->formData['min_transaksi'] ?: null;
                $promoData['kuota_total'] = $this->formData['kuota_total'] ?: null;
                $promoData['max_per_user'] = $this->formData['max_per_user'] ?: null;

                Promo::create($promoData);
            });

            $this->success('Promo berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
        } catch (QueryException $e) {
            Log::error('Promo Create: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan promo. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Promo Create: Unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);

            $this->error('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.promo.create');
    }
}

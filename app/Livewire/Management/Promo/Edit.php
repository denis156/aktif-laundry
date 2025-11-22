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

#[Title('Edit Promo')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public int $promoId;

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
        'kuota_terpakai' => 0,
        'max_per_user' => null,
        'berlaku_untuk' => 'semua',
        'status' => 'Aktif',
    ];

    public function mount(int $id): void
    {
        $this->promoId = $id;
        $this->loadPromo();
    }

    protected function loadPromo(): void
    {
        try {
            $promo = Promo::findOrFail($this->promoId);

            $this->formData = [
                'kode_promo' => $promo->kode_promo,
                'nama_promo' => $promo->nama_promo,
                'deskripsi' => $promo->deskripsi ?? '',
                'tipe_diskon' => $promo->tipe_diskon,
                'nilai_diskon' => $promo->nilai_diskon,
                'diskon_maksimal' => $promo->diskon_maksimal,
                'min_transaksi' => $promo->min_transaksi,
                'tanggal_mulai' => $promo->tanggal_mulai->format('Y-m-d'),
                'tanggal_berakhir' => $promo->tanggal_berakhir->format('Y-m-d'),
                'kuota_total' => $promo->kuota_total,
                'kuota_terpakai' => $promo->kuota_terpakai,
                'max_per_user' => $promo->max_per_user,
                'berlaku_untuk' => $promo->berlaku_untuk,
                'status' => $promo->status,
            ];
        } catch (Exception $e) {
            Log::error('Promo Edit: Failed to load promo', [
                'promo_id' => $this->promoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Promo tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
        }
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
            'formData.kode_promo' => 'required|string|max:50|unique:promo,kode_promo,'.$this->promoId,
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
            'formData.status' => 'required|in:Aktif,Tidak Aktif,Habis',
        ];

        $this->validate($rules);

        try {
            DB::transaction(function () {
                $promo = Promo::findOrFail($this->promoId);

                $promoData = $this->formData;

                // Convert null string to actual null
                $promoData['diskon_maksimal'] = $this->formData['diskon_maksimal'] ?: null;
                $promoData['min_transaksi'] = $this->formData['min_transaksi'] ?: null;
                $promoData['kuota_total'] = $this->formData['kuota_total'] ?: null;
                $promoData['max_per_user'] = $this->formData['max_per_user'] ?: null;

                // Jangan ubah kuota_terpakai
                unset($promoData['kuota_terpakai']);

                $promo->update($promoData);
            });

            $this->success('Promo berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/promo', navigate: true);
        } catch (QueryException $e) {
            Log::error('Promo Edit: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan promo. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Promo Edit: Unexpected error', [
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
        return view('livewire.management.promo.edit');
    }
}

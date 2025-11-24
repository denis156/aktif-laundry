<?php

declare(strict_types=1);

namespace App\Livewire\Management\Referral;

use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PromoHelper;
use App\Helper\Database\ReferralHelper;
use App\Models\Referral;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Tambah Referral')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

    public array $formData = [
        'pelanggan_id' => null,
        'promo_referrer_id' => null,
        'promo_referee_id' => null,
        'kode_referral' => '',
        'poin_referrer' => 10,
        'diskon_referee' => 10,
        'min_transaksi_referee' => null,
        'status' => 'Aktif',
    ];

    public string $pelangganSearch = '';

    public function mount(): void
    {
        $this->refreshKodeReferral();
    }

    public function refreshKodeReferral(): void
    {
        try {
            $this->formData['kode_referral'] = ReferralHelper::generateKodeReferral();
        } catch (Exception $e) {
            Log::error('Failed to generate kode referral', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode referral. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function save(): void
    {
        $rules = [
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.promo_referrer_id' => 'nullable|exists:promo,id',
            'formData.promo_referee_id' => 'nullable|exists:promo,id',
            'formData.kode_referral' => 'required|string|max:50|unique:referral,kode_referral',
            'formData.poin_referrer' => 'nullable|integer|min:0',
            'formData.diskon_referee' => 'nullable|integer|min:0',
            'formData.min_transaksi_referee' => 'nullable|integer|min:1',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ];

        $this->validate($rules);

        try {
            DB::transaction(function () {
                // Check if pelanggan already has a referral
                if (Referral::where('pelanggan_id', $this->formData['pelanggan_id'])->exists()) {
                    throw new Exception('Pelanggan ini sudah memiliki kode referral');
                }

                // Check if kode exists, regenerate if needed
                if (Referral::where('kode_referral', $this->formData['kode_referral'])->exists()) {
                    $this->refreshKodeReferral();
                }

                $referralData = $this->formData;
                $referralData['total_referral'] = 0;
                $referralData['total_poin'] = 0;
                $referralData['total_berhasil'] = 0;

                // Convert empty to null
                $referralData['promo_referrer_id'] = $this->formData['promo_referrer_id'] ?: null;
                $referralData['promo_referee_id'] = $this->formData['promo_referee_id'] ?: null;
                $referralData['min_transaksi_referee'] = $this->formData['min_transaksi_referee'] ?: null;
                $referralData['poin_referrer'] = $this->formData['poin_referrer'] ?: 0;
                $referralData['diskon_referee'] = $this->formData['diskon_referee'] ?: 0;

                Referral::create($referralData);
            });

            $this->success('Referral berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/referral', navigate: true);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                Log::warning('Duplicate entry detected when creating referral', [
                    'kode_referral' => $this->formData['kode_referral'],
                    'error' => $e->getMessage(),
                ]);
                $this->refreshKodeReferral();
                $this->warning('Kode referral di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            Log::error('Referral Create: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan referral. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Referral Create: Unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);

            $this->error($e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.referral.create', [
            'pelangganOptions' => PelangganHelper::getPelangganOptions($this->pelangganSearch),
            'promoOptions' => PromoHelper::getPromoOptions(),
            'statusOptions' => ReferralHelper::getStatusOptions(),
        ]);
    }
}

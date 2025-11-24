<?php

declare(strict_types=1);

namespace App\Livewire\Management\Referral;

use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PromoHelper;
use App\Helper\Database\ReferralHelper;
use App\Models\Referral;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Edit Referral')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public int $referralId;

    public array $formData = [
        'pelanggan_id' => null,
        'promo_referrer_id' => null,
        'promo_referee_id' => null,
        'kode_referral' => '',
        'poin_referrer' => 10,
        'diskon_referee' => 10,
        'min_transaksi_referee' => null,
        'total_referral' => 0,
        'total_poin' => 0,
        'total_berhasil' => 0,
        'status' => 'Aktif',
    ];

    public string $pelangganSearch = '';

    public ?string $pelangganNama = null;

    public function mount(int $id): void
    {
        $this->referralId = $id;
        $this->loadReferral();
    }

    protected function loadReferral(): void
    {
        try {
            $referral = Referral::with(['pelanggan', 'promoReferrer', 'promoReferee'])->findOrFail($this->referralId);

            $this->formData = [
                'pelanggan_id' => $referral->pelanggan_id,
                'promo_referrer_id' => $referral->promo_referrer_id,
                'promo_referee_id' => $referral->promo_referee_id,
                'kode_referral' => $referral->kode_referral,
                'poin_referrer' => $referral->poin_referrer,
                'diskon_referee' => $referral->diskon_referee,
                'min_transaksi_referee' => $referral->min_transaksi_referee,
                'total_referral' => $referral->total_referral,
                'total_poin' => $referral->total_poin,
                'total_berhasil' => $referral->total_berhasil,
                'status' => $referral->status,
            ];

            $this->pelangganNama = $referral->pelanggan->nama ?? null;
        } catch (Exception $e) {
            Log::error('Referral Edit: Failed to load referral', [
                'referral_id' => $this->referralId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Referral tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/referral', navigate: true);
        }
    }

    public function save(): void
    {
        $rules = [
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.promo_referrer_id' => 'nullable|exists:promo,id',
            'formData.promo_referee_id' => 'nullable|exists:promo,id',
            'formData.kode_referral' => 'required|string|max:50|unique:referral,kode_referral,'.$this->referralId,
            'formData.poin_referrer' => 'nullable|integer|min:0',
            'formData.diskon_referee' => 'nullable|integer|min:0',
            'formData.min_transaksi_referee' => 'nullable|integer|min:1',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
        ];

        $this->validate($rules);

        try {
            $referral = Referral::findOrFail($this->referralId);

            // Check if pelanggan is changing and already has another referral
            if ($referral->pelanggan_id !== $this->formData['pelanggan_id']) {
                if (Referral::where('pelanggan_id', $this->formData['pelanggan_id'])->exists()) {
                    $this->error('Pelanggan ini sudah memiliki kode referral lain', position: 'toast-bottom');

                    return;
                }
            }

            $referralData = $this->formData;

            // Remove counters from update
            unset($referralData['total_referral'], $referralData['total_poin'], $referralData['total_berhasil']);

            // Convert empty to null
            $referralData['promo_referrer_id'] = $this->formData['promo_referrer_id'] ?: null;
            $referralData['promo_referee_id'] = $this->formData['promo_referee_id'] ?: null;
            $referralData['min_transaksi_referee'] = $this->formData['min_transaksi_referee'] ?: null;
            $referralData['poin_referrer'] = $this->formData['poin_referrer'] ?: 0;
            $referralData['diskon_referee'] = $this->formData['diskon_referee'] ?: 0;

            $referral->update($referralData);

            $this->success('Referral berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/referral', navigate: true);
        } catch (QueryException $e) {
            Log::error('Referral Edit: Database error', [
                'referral_id' => $this->referralId,
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan referral. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Referral Edit: Unexpected error', [
                'referral_id' => $this->referralId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);

            $this->error('Gagal menyimpan referral. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.referral.edit', [
            'pelangganOptions' => PelangganHelper::getPelangganOptions($this->pelangganSearch),
            'promoOptions' => PromoHelper::getPromoOptions(),
            'statusOptions' => ReferralHelper::getStatusOptions(),
        ]);
    }
}

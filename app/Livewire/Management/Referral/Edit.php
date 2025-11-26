<?php

declare(strict_types=1);

namespace App\Livewire\Management\Referral;

use App\Helper\Database\PromoHelper;
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

    public ?int $promo_referrer_id = null;

    public ?int $promo_referee_id = null;

    public ?string $campaign_source = null;

    // * Read-only data untuk display
    public string $kode_referral = '';

    public string $pelanggan_nama = '';

    public int $total_referral = 0;

    public int $total_berhasil = 0;

    public function mount(int $id): void
    {
        $this->referralId = $id;
        $this->loadReferral();
    }

    protected function loadReferral(): void
    {
        try {
            $referral = Referral::with('pelanggan')->findOrFail($this->referralId);

            // Editable fields
            $this->promo_referrer_id = $referral->promo_referrer_id;
            $this->promo_referee_id = $referral->promo_referee_id;
            $this->campaign_source = $referral->campaign_source;

            // Read-only data for display
            $this->kode_referral = $referral->kode_referral;
            $this->pelanggan_nama = $referral->pelanggan->nama ?? 'Unknown';
            $this->total_referral = $referral->total_referral;
            $this->total_berhasil = $referral->total_berhasil;
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
        $this->validate([
            'promo_referrer_id' => ['nullable', 'exists:promo,id'],
            'promo_referee_id' => ['nullable', 'exists:promo,id'],
            'campaign_source' => ['nullable', 'string', 'max:100'],
        ], [
            'promo_referrer_id.exists' => 'Promo referrer tidak valid',
            'promo_referee_id.exists' => 'Promo referee tidak valid',
            'campaign_source.max' => 'Campaign source maksimal 100 karakter',
        ]);

        try {
            $referral = Referral::findOrFail($this->referralId);

            $referral->update([
                'promo_referrer_id' => $this->promo_referrer_id,
                'promo_referee_id' => $this->promo_referee_id,
                'campaign_source' => $this->campaign_source,
            ]);

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
            ]);

            $this->error('Gagal menyimpan referral. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.referral.edit', [
            'promoOptions' => PromoHelper::getPromoOptions(),
        ]);
    }
}

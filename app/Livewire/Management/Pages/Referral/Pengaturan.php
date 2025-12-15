<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Referral;

use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\PromoHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Konfigurasi Referral')]
#[Layout('layouts.management.app')]
class Pengaturan extends Component
{
    use Toast;

    public ?int $default_promo_referrer_id = null;

    public ?int $default_promo_referee_id = null;

    private array $originalValues = [];

    public function mount(): void
    {
        $this->loadSettings();
        $this->saveOriginalValues();
    }

    protected function loadSettings(): void
    {
        $this->default_promo_referrer_id = PengaturanHelper::getValue('default_promo_referrer_id', null);
        $this->default_promo_referee_id = PengaturanHelper::getValue('default_promo_referee_id', null);
    }

    protected function saveOriginalValues(): void
    {
        $this->originalValues = [
            'default_promo_referrer_id' => $this->default_promo_referrer_id,
            'default_promo_referee_id' => $this->default_promo_referee_id,
        ];
    }

    public function hasChanges(): bool
    {
        if (empty($this->originalValues)) {
            return false;
        }

        return $this->default_promo_referrer_id !== ($this->originalValues['default_promo_referrer_id'] ?? null)
            || $this->default_promo_referee_id !== ($this->originalValues['default_promo_referee_id'] ?? null);
    }

    public function save(): void
    {
        $this->validate([
            'default_promo_referrer_id' => ['nullable', 'integer', 'exists:promo,id'],
            'default_promo_referee_id' => ['nullable', 'integer', 'exists:promo,id'],
        ], [
            'default_promo_referrer_id.exists' => 'Promo referrer tidak valid',
            'default_promo_referee_id.exists' => 'Promo referee tidak valid',
        ]);

        try {
            DB::beginTransaction();

            PengaturanHelper::setValue(
                'default_promo_referrer_id',
                $this->default_promo_referrer_id ?? null,
                'number',
                'referral',
                'Promo default untuk referrer (yang mengajak)'
            );

            PengaturanHelper::setValue(
                'default_promo_referee_id',
                $this->default_promo_referee_id ?? null,
                'number',
                'referral',
                'Promo default untuk referee (yang diajak)'
            );

            // Update semua referral yang belum punya promo khusus
            $this->applyDefaultPromoToAllReferrals();

            DB::commit();

            $this->saveOriginalValues();

            $this->success('Konfigurasi referral berhasil disimpan dan diterapkan ke semua pelanggan!', position: 'toast-bottom');
        } catch (Exception $e) {
            DB::rollBack();

            $this->error(
                'Gagal menyimpan konfigurasi: '.$e->getMessage(),
                position: 'toast-bottom'
            );
        }
    }

    protected function applyDefaultPromoToAllReferrals(): void
    {
        // Update referral yang promo_referrer_id nya null
        if ($this->default_promo_referrer_id) {
            DB::table('referral')
                ->whereNull('promo_referrer_id')
                ->update(['promo_referrer_id' => $this->default_promo_referrer_id]);
        }

        // Update referral yang promo_referee_id nya null
        if ($this->default_promo_referee_id) {
            DB::table('referral')
                ->whereNull('promo_referee_id')
                ->update(['promo_referee_id' => $this->default_promo_referee_id]);
        }
    }

    public function resetChanges(): void
    {
        if (empty($this->originalValues)) {
            $this->warning('Tidak ada perubahan untuk direset', position: 'toast-bottom');

            return;
        }

        $this->default_promo_referrer_id = $this->originalValues['default_promo_referrer_id'] ?? null;
        $this->default_promo_referee_id = $this->originalValues['default_promo_referee_id'] ?? null;

        $this->success('Perubahan dibatalkan', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.referral.pengaturan', [
            'hasChanges' => $this->hasChanges(),
            'promoOptions' => PromoHelper::getPromoOptions(),
            'isReferralEnabled' => (bool) PengaturanHelper::getValue('enable_referral', true),
        ]);
    }
}

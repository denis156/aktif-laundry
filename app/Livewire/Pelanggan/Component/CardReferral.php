<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\PengaturanHelper;
use App\Models\Promo;
use App\Models\Referral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Mary\Traits\Toast;

class CardReferral extends Component
{
    use Toast;

    public bool $confirmGenerateModal = false;

    #[Computed]
    public function isReferralEnabled(): bool
    {
        return (bool) PengaturanHelper::getValue('enable_referral', true);
    }

    #[Computed]
    public function referral(): ?Referral
    {
        $pelangganId = Auth::guard('pelanggan')->id();

        if (! $pelangganId) {
            return null;
        }

        return Referral::with(['promoReferrer', 'promoReferee'])
            ->where('pelanggan_id', $pelangganId)
            ->first();
    }

    #[Computed]
    public function kodeReferral(): string
    {
        return $this->referral()?->kode_referral ?? 'LOADING...';
    }

    #[Computed]
    public function benefitReferrer(): array
    {
        // Coba ambil dari referral dulu
        $promo = $this->referral()?->promoReferrer;

        // Jika tidak ada, ambil dari default pengaturan
        if (! $promo) {
            $defaultPromoId = PengaturanHelper::getValue('default_promo_referrer_id', null);
            if ($defaultPromoId) {
                $promo = Promo::find($defaultPromoId);
            }
        }

        if (! $promo) {
            return [
                'Dapatkan promo spesial',
                'Ajak teman lebih banyak',
                'Nikmati berbagai keuntungan',
            ];
        }

        return $this->extractBenefits($promo);
    }

    #[Computed]
    public function benefitReferee(): array
    {
        // Coba ambil dari referral dulu
        $promo = $this->referral()?->promoReferee;

        // Jika tidak ada, ambil dari default pengaturan
        if (! $promo) {
            $defaultPromoId = PengaturanHelper::getValue('default_promo_referee_id', null);
            if ($defaultPromoId) {
                $promo = Promo::find($defaultPromoId);
            }
        }

        if (! $promo) {
            return [
                'Diskon untuk pendaftar baru',
                'Promo khusus first order',
            ];
        }

        return $this->extractBenefits($promo);
    }

    protected function extractBenefits(Promo $promo): array
    {
        // Tampilkan nama promo sebagai benefit utama
        return [$promo->nama_promo];
    }

    public function salinKode(): void
    {
        $this->dispatch('copy-to-clipboard', text: $this->kodeReferral());
        $this->success('Kode referral berhasil disalin!', position: 'toast-top');
    }

    public function confirmGenerate(): void
    {
        $this->confirmGenerateModal = true;
    }

    public function generateBaru(): void
    {
        $referral = $this->referral();

        if (! $referral) {
            $this->error('Referral tidak ditemukan', position: 'toast-top');
            $this->confirmGenerateModal = false;

            return;
        }

        // Generate kode baru yang unik
        do {
            $newKode = strtoupper(Str::random(8));
        } while (Referral::where('kode_referral', $newKode)->exists());

        $referral->update(['kode_referral' => $newKode]);

        // Reset computed property
        unset($this->referral);
        unset($this->kodeReferral);

        $this->confirmGenerateModal = false;
        $this->success('Kode referral baru berhasil dibuat!', position: 'toast-top');
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.component.card-referral');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class ListPromo extends Component
{
    use Toast;

    public function getBannerUrl(Promo $promo): string
    {
        if ($promo->banner_image) {
            // Cek apakah banner_image adalah URL external (http:// atau https://)
            if (str_starts_with($promo->banner_image, 'http://') || str_starts_with($promo->banner_image, 'https://')) {
                return $promo->banner_image;
            }

            // Jika bukan URL external, anggap sebagai path lokal di storage
            return asset('storage/'.$promo->banner_image);
        }

        return asset('images/Logo.png');
    }

    public function usePromo(int $promoId): void
    {
        $promo = Promo::find($promoId);

        if (! $promo) {
            $this->error('Promo tidak ditemukan', position: 'toast-top');

            return;
        }

        // Store promo ID in session
        session(['selected_promo_id' => $promo->id]);

        $this->success('Promo '.$promo->kode_promo.' dipilih! Silakan pilih layanan.', position: 'toast-top');
        $this->redirect(route('pesanan.pelanggan'), navigate: true);
    }

    public function render(): mixed
    {
        $pelangganId = Auth::guard('pelanggan')->id();

        $promoList = Promo::where('status', 'Aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->filter(function (Promo $promo) use ($pelangganId) {
                // Cek masih ada kuota
                if (! PromoHelper::hasQuota($promo)) {
                    return false;
                }

                // Jika user login, filter berdasarkan pelanggan
                if ($pelangganId) {
                    // Cek apakah pelanggan di-exclude
                    if (PromoHelper::isPelangganExcluded($promo, $pelangganId)) {
                        return false;
                    }

                    // Cek apakah pelanggan masih bisa pakai promo (max_per_user)
                    if (! PromoHelper::canUserUsePromo($promo, $pelangganId)) {
                        return false;
                    }
                }

                return true;
            });

        return view('livewire.pelanggan.component.list-promo', [
            'promoList' => $promoList,
        ]);
    }
}

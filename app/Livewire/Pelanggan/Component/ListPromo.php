<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Component;

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ListPromo extends Component
{
    public function render(): mixed
    {
        $promoList = Promo::where('status', 'Aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->filter(fn ($promo) => PromoHelper::hasQuota($promo))
            ->map(function (Promo $promo) {
                $bannerPath = PromoHelper::getBannerImage($promo);

                return [
                    'id' => $promo->id,
                    'kode_promo' => $promo->kode_promo,
                    'nama_promo' => $promo->nama_promo,
                    'deskripsi' => $promo->deskripsi,
                    'tipe_diskon' => $promo->tipe_diskon,
                    'nilai_diskon' => $promo->nilai_diskon,
                    'nilai_diskon_formatted' => PromoHelper::formatNilaiDiskon($promo->tipe_diskon, $promo->nilai_diskon),
                    'tanggal_berakhir' => $promo->tanggal_berakhir->format('d M Y'),
                    'min_transaksi' => $promo->min_transaksi,
                    'min_transaksi_formatted' => $promo->min_transaksi ? 'Rp '.number_format($promo->min_transaksi, 0, ',', '.') : null,
                    'min_berat' => PromoHelper::getMinBerat($promo),
                    'max_berat' => PromoHelper::getMaxBerat($promo),
                    'berlaku_untuk' => $promo->berlaku_untuk,
                    'berlaku_untuk_label' => match ($promo->berlaku_untuk) {
                        'semua' => 'Semua Pelanggan',
                        'pelanggan_baru' => 'Pelanggan Baru',
                        'layanan_tertentu' => 'Layanan Tertentu',
                        default => $promo->berlaku_untuk,
                    },
                    'kuota_total' => $promo->kuota_total,
                    'kuota_terpakai' => $promo->kuota_terpakai,
                    'kuota_tersisa' => $promo->kuota_total ? ($promo->kuota_total - $promo->kuota_terpakai) : null,
                    'banner_url' => $bannerPath ? Storage::url($bannerPath) : 'https://placehold.co/600x400/png',
                    'terms_conditions' => PromoHelper::getTermsConditions($promo),
                    'auto_apply' => PromoHelper::isAutoApply($promo),
                ];
            })
            ->values();

        return view('livewire.pelanggan.component.list-promo', [
            'promoList' => $promoList,
        ]);
    }
}

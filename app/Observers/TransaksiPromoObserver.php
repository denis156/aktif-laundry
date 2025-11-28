<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Promo;
use App\Models\TransaksiPromo;
use Illuminate\Support\Facades\Log;

// ! Observer untuk TransaksiPromo
// ? Otomatis increment/decrement kuota promo saat transaksi promo dibuat/dihapus

class TransaksiPromoObserver
{
    /**
     * Handle the TransaksiPromo "created" event.
     * Auto-increment kuota_terpakai dan update status jika habis
     */
    public function created(TransaksiPromo $transaksiPromo): void
    {
        try {
            $promo = Promo::find($transaksiPromo->promo_id);

            if (! $promo) {
                Log::warning('TransaksiPromoObserver: Promo not found', [
                    'transaksi_promo_id' => $transaksiPromo->id,
                    'promo_id' => $transaksiPromo->promo_id,
                ]);

                return;
            }

            // Increment kuota terpakai
            $promo->increment('kuota_terpakai');

            Log::info('TransaksiPromoObserver: Kuota incremented', [
                'promo_id' => $promo->id,
                'kode_promo' => $promo->kode_promo,
                'kuota_terpakai' => $promo->kuota_terpakai + 1,
                'kuota_total' => $promo->kuota_total,
            ]);

            // Cek apakah kuota sudah habis
            if ($promo->kuota_total && $promo->kuota_terpakai >= $promo->kuota_total) {
                $promo->update(['status' => 'Habis']);

                Log::info('TransaksiPromoObserver: Promo status changed to Habis', [
                    'promo_id' => $promo->id,
                    'kode_promo' => $promo->kode_promo,
                    'kuota_terpakai' => $promo->kuota_terpakai,
                    'kuota_total' => $promo->kuota_total,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TransaksiPromoObserver: Failed to increment promo usage', [
                'transaksi_promo_id' => $transaksiPromo->id,
                'promo_id' => $transaksiPromo->promo_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the TransaksiPromo "deleted" event.
     * Auto-decrement kuota_terpakai dan update status jika perlu
     */
    public function deleted(TransaksiPromo $transaksiPromo): void
    {
        try {
            $promo = Promo::withTrashed()->find($transaksiPromo->promo_id);

            if (! $promo) {
                Log::warning('TransaksiPromoObserver: Promo not found on delete', [
                    'transaksi_promo_id' => $transaksiPromo->id,
                    'promo_id' => $transaksiPromo->promo_id,
                ]);

                return;
            }

            // Decrement kuota terpakai (jangan sampai negatif)
            if ($promo->kuota_terpakai > 0) {
                $promo->decrement('kuota_terpakai');

                Log::info('TransaksiPromoObserver: Kuota decremented', [
                    'promo_id' => $promo->id,
                    'kode_promo' => $promo->kode_promo,
                    'kuota_terpakai' => $promo->kuota_terpakai - 1,
                    'kuota_total' => $promo->kuota_total,
                ]);

                // Jika status Habis tapi setelah decrement masih ada kuota, kembalikan ke Aktif
                if ($promo->status === 'Habis' && (! $promo->kuota_total || $promo->kuota_terpakai < $promo->kuota_total)) {
                    $promo->update(['status' => 'Aktif']);

                    Log::info('TransaksiPromoObserver: Promo status changed back to Aktif', [
                        'promo_id' => $promo->id,
                        'kode_promo' => $promo->kode_promo,
                        'kuota_terpakai' => $promo->kuota_terpakai,
                        'kuota_total' => $promo->kuota_total,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('TransaksiPromoObserver: Failed to decrement promo usage', [
                'transaksi_promo_id' => $transaksiPromo->id,
                'promo_id' => $transaksiPromo->promo_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the TransaksiPromo "restored" event.
     * Sama seperti created, increment kuota
     */
    public function restored(TransaksiPromo $transaksiPromo): void
    {
        $this->created($transaksiPromo);
    }
}

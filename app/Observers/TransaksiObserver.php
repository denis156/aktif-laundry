<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Log;

class TransaksiObserver
{
    /**
     * Handle the Transaksi "created" event.
     * Increment total_transaksi pada Pelanggan
     */
    public function created(Transaksi $transaksi): void
    {
        if ($transaksi->pelanggan_id) {
            try {
                $pelanggan = Pelanggan::find($transaksi->pelanggan_id);
                if ($pelanggan) {
                    $pelanggan->increment('total_transaksi');

                    Log::info('Pelanggan total_transaksi incremented', [
                        'pelanggan_id' => $pelanggan->id,
                        'transaksi_id' => $transaksi->id,
                        'new_total' => $pelanggan->total_transaksi,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to increment pelanggan total_transaksi', [
                    'pelanggan_id' => $transaksi->pelanggan_id,
                    'transaksi_id' => $transaksi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaksi "updated" event.
     * Jika pelanggan_id berubah, update total_transaksi di kedua pelanggan
     */
    public function updated(Transaksi $transaksi): void
    {
        if ($transaksi->isDirty('pelanggan_id')) {
            try {
                // Decrement dari pelanggan lama
                $oldPelangganId = $transaksi->getOriginal('pelanggan_id');
                if ($oldPelangganId) {
                    $oldPelanggan = Pelanggan::find($oldPelangganId);
                    if ($oldPelanggan && $oldPelanggan->total_transaksi > 0) {
                        $oldPelanggan->decrement('total_transaksi');
                    }
                }

                // Increment ke pelanggan baru
                if ($transaksi->pelanggan_id) {
                    $newPelanggan = Pelanggan::find($transaksi->pelanggan_id);
                    if ($newPelanggan) {
                        $newPelanggan->increment('total_transaksi');
                    }
                }

                Log::info('Pelanggan total_transaksi updated due to transaksi change', [
                    'transaksi_id' => $transaksi->id,
                    'old_pelanggan_id' => $oldPelangganId,
                    'new_pelanggan_id' => $transaksi->pelanggan_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update pelanggan total_transaksi', [
                    'transaksi_id' => $transaksi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaksi "deleted" event.
     * Decrement total_transaksi pada Pelanggan (soft delete)
     */
    public function deleted(Transaksi $transaksi): void
    {
        if ($transaksi->pelanggan_id) {
            try {
                $pelanggan = Pelanggan::find($transaksi->pelanggan_id);
                if ($pelanggan && $pelanggan->total_transaksi > 0) {
                    $pelanggan->decrement('total_transaksi');

                    Log::info('Pelanggan total_transaksi decremented', [
                        'pelanggan_id' => $pelanggan->id,
                        'transaksi_id' => $transaksi->id,
                        'new_total' => $pelanggan->total_transaksi,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to decrement pelanggan total_transaksi', [
                    'pelanggan_id' => $transaksi->pelanggan_id,
                    'transaksi_id' => $transaksi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaksi "restored" event.
     * Increment kembali total_transaksi saat transaksi di-restore
     */
    public function restored(Transaksi $transaksi): void
    {
        if ($transaksi->pelanggan_id) {
            try {
                $pelanggan = Pelanggan::find($transaksi->pelanggan_id);
                if ($pelanggan) {
                    $pelanggan->increment('total_transaksi');

                    Log::info('Pelanggan total_transaksi incremented (restored)', [
                        'pelanggan_id' => $pelanggan->id,
                        'transaksi_id' => $transaksi->id,
                        'new_total' => $pelanggan->total_transaksi,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to increment pelanggan total_transaksi (restored)', [
                    'pelanggan_id' => $transaksi->pelanggan_id,
                    'transaksi_id' => $transaksi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Transaksi "force deleted" event.
     * Decrement total_transaksi saat transaksi di-force delete (permanent delete)
     */
    public function forceDeleted(Transaksi $transaksi): void
    {
        if ($transaksi->pelanggan_id) {
            try {
                $pelanggan = Pelanggan::find($transaksi->pelanggan_id);
                if ($pelanggan && $pelanggan->total_transaksi > 0) {
                    $pelanggan->decrement('total_transaksi');

                    Log::info('Pelanggan total_transaksi decremented (force deleted)', [
                        'pelanggan_id' => $pelanggan->id,
                        'transaksi_id' => $transaksi->id,
                        'new_total' => $pelanggan->total_transaksi,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to decrement pelanggan total_transaksi (force deleted)', [
                    'pelanggan_id' => $transaksi->pelanggan_id,
                    'transaksi_id' => $transaksi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

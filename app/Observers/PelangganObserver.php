<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Pelanggan;
use Illuminate\Support\Facades\Storage;

class PelangganObserver
{
    /**
     * Handle the Pelanggan "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(Pelanggan $pelanggan): void
    {
        // Cek apakah avatar_url berubah
        if ($pelanggan->isDirty('avatar_url')) {
            // Ambil avatar lama dari database (original)
            $oldAvatar = $pelanggan->getOriginal('avatar_url');

            // Hapus avatar lama jika ada
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }
    }

    /**
     * Handle the Pelanggan "deleted" event.
     * Hapus avatar saat pelanggan dihapus
     */
    public function deleted(Pelanggan $pelanggan): void
    {
        // Hapus avatar jika ada
        if ($pelanggan->avatar_url && Storage::disk('public')->exists($pelanggan->avatar_url)) {
            Storage::disk('public')->delete($pelanggan->avatar_url);
        }
    }

    /**
     * Handle the Pelanggan "force deleted" event.
     * Hapus avatar saat pelanggan di-force delete (soft delete)
     */
    public function forceDeleted(Pelanggan $pelanggan): void
    {
        // Hapus avatar jika ada
        if ($pelanggan->avatar_url && Storage::disk('public')->exists($pelanggan->avatar_url)) {
            Storage::disk('public')->delete($pelanggan->avatar_url);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Kurir;
use Illuminate\Support\Facades\Storage;

class KurirObserver
{
    /**
     * Handle the Kurir "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(Kurir $kurir): void
    {
        // Cek apakah avatar_url berubah
        if ($kurir->isDirty('avatar_url')) {
            // Ambil avatar lama dari database (original)
            $oldAvatar = $kurir->getOriginal('avatar_url');

            // Hapus avatar lama jika ada
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }
    }

    /**
     * Handle the Kurir "deleted" event.
     * Hapus avatar saat kurir dihapus
     */
    public function deleted(Kurir $kurir): void
    {
        // Hapus avatar jika ada
        if ($kurir->avatar_url && Storage::disk('public')->exists($kurir->avatar_url)) {
            Storage::disk('public')->delete($kurir->avatar_url);
        }
    }

    /**
     * Handle the Kurir "force deleted" event.
     * Hapus avatar saat kurir di-force delete (soft delete)
     */
    public function forceDeleted(Kurir $kurir): void
    {
        // Hapus avatar jika ada
        if ($kurir->avatar_url && Storage::disk('public')->exists($kurir->avatar_url)) {
            Storage::disk('public')->delete($kurir->avatar_url);
        }
    }
}

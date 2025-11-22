<?php

namespace App\Observers;

use App\Helper\AddressMetadata;
use App\Models\Kurir;
use Illuminate\Support\Facades\Storage;

class KurirObserver
{
    /**
     * Handle the Kurir "saving" event.
     * Auto-sync kolom alamat dari metadata sebelum save
     */
    public function saving(Kurir $kurir): void
    {
        // Auto-sync kolom alamat dari metadata
        $metadata = $kurir->metadata ?? [];

        if (! empty($metadata['detail_alamat']) || ! empty($metadata['kelurahan']) || ! empty($metadata['kecamatan'])) {
            AddressMetadata::syncAlamatColumn($kurir);
        }
    }

    /**
     * Handle the Kurir "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(Kurir $kurir): void
    {
        // Cek apakah avatar_url di metadata berubah
        if ($kurir->isDirty('metadata')) {
            $originalMetadata = $kurir->getOriginal('metadata') ?? [];
            $newMetadata = $kurir->metadata ?? [];

            $oldAvatar = $originalMetadata['avatar_url'] ?? null;
            $newAvatar = $newMetadata['avatar_url'] ?? null;

            // Jika avatar berubah, hapus avatar lama
            if ($oldAvatar && $oldAvatar !== $newAvatar && Storage::disk('public')->exists($oldAvatar)) {
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
        $metadata = $kurir->metadata ?? [];
        $avatarUrl = $metadata['avatar_url'] ?? null;

        if ($avatarUrl && Storage::disk('public')->exists($avatarUrl)) {
            Storage::disk('public')->delete($avatarUrl);
        }
    }

    /**
     * Handle the Kurir "force deleted" event.
     * Hapus avatar saat kurir di-force delete (soft delete)
     */
    public function forceDeleted(Kurir $kurir): void
    {
        // Hapus avatar jika ada
        $metadata = $kurir->metadata ?? [];
        $avatarUrl = $metadata['avatar_url'] ?? null;

        if ($avatarUrl && Storage::disk('public')->exists($avatarUrl)) {
            Storage::disk('public')->delete($avatarUrl);
        }
    }
}

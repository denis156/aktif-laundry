<?php

namespace App\Observers;

use App\Helper\AddressMetadata;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Storage;

class PelangganObserver
{
    /**
     * Handle the Pelanggan "saving" event.
     * Auto-sync kolom alamat dari metadata sebelum save
     */
    public function saving(Pelanggan $pelanggan): void
    {
        // Auto-sync kolom alamat dari metadata
        $metadata = $pelanggan->metadata ?? [];

        if (! empty($metadata['detail_alamat']) || ! empty($metadata['kelurahan']) || ! empty($metadata['kecamatan'])) {
            AddressMetadata::syncAlamatColumn($pelanggan);
        }
    }

    /**
     * Handle the Pelanggan "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(Pelanggan $pelanggan): void
    {
        // Cek apakah avatar_url di metadata berubah
        if ($pelanggan->isDirty('metadata')) {
            $originalMetadata = $pelanggan->getOriginal('metadata') ?? [];
            $newMetadata = $pelanggan->metadata ?? [];

            $oldAvatar = $originalMetadata['avatar_url'] ?? null;
            $newAvatar = $newMetadata['avatar_url'] ?? null;

            // Jika avatar berubah, hapus avatar lama
            if ($oldAvatar && $oldAvatar !== $newAvatar && Storage::disk('public')->exists($oldAvatar)) {
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
        $metadata = $pelanggan->metadata ?? [];
        $avatarUrl = $metadata['avatar_url'] ?? null;

        if ($avatarUrl && Storage::disk('public')->exists($avatarUrl)) {
            Storage::disk('public')->delete($avatarUrl);
        }
    }

    /**
     * Handle the Pelanggan "force deleted" event.
     * Hapus avatar saat pelanggan di-force delete (soft delete)
     */
    public function forceDeleted(Pelanggan $pelanggan): void
    {
        // Hapus avatar jika ada
        $metadata = $pelanggan->metadata ?? [];
        $avatarUrl = $metadata['avatar_url'] ?? null;

        if ($avatarUrl && Storage::disk('public')->exists($avatarUrl)) {
            Storage::disk('public')->delete($avatarUrl);
        }
    }
}

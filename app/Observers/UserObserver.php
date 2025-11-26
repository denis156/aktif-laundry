<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(User $user): void
    {
        // Cek apakah avatar_url berubah
        if ($user->isDirty('avatar_url')) {
            // Ambil avatar lama dari database (original)
            $oldAvatar = $user->getOriginal('avatar_url');

            // Hapus avatar lama jika ada
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     * Hapus avatar saat user dihapus
     */
    public function deleted(User $user): void
    {
        // Hapus avatar jika ada
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }
    }

    /**
     * Handle the User "force deleted" event.
     * Hapus avatar saat user di-force delete (soft delete)
     */
    public function forceDeleted(User $user): void
    {
        // Hapus avatar jika ada
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }
    }
}

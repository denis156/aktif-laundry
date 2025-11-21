<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\User;
use App\Helper\AddressMetadata;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola data User
//
// ? Metadata yang disimpan:
// * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
// * - kelurahan: Kelurahan/Desa
// * - kecamatan: Kecamatan
// * - kabupaten_kota: Kabupaten/Kota
// * - provinsi: Provinsi
// * - latitude: Koordinat GPS latitude
// * - longitude: Koordinat GPS longitude
// * - jam_masuk: Jam masuk kerja (HH:MM)
// * - jam_keluar: Jam keluar kerja (HH:MM)
// * - gaji: Gaji pokok
// * - target_bulanan: Target penjualan/kinerja bulanan

class UserHelper
{
    // * Password validation constants
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 255;

    // * Avatar upload constants (in KB)
    public const AVATAR_MAX_SIZE_KB = 2048; // 2 MB

    // * Metadata constants
    public const META_GAJI = 'gaji';

    // * Ambil nilai dari metadata
    public static function getMetadata(User $user, string $key, mixed $default = null): mixed
    {
        return data_get($user->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(User $user, string $key, mixed $value): void
    {
        $metadata = $user->metadata ?? [];
        data_set($metadata, $key, $value);
        $user->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(User $user, array $data): void
    {
        $user->metadata = array_merge($user->metadata ?? [], $data);
    }

    // * Set alamat regional ke metadata dan sync ke kolom alamat
    public static function setAlamatRegional(
        User $user,
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): void {
        try {
            // Set metadata alamat menggunakan AddressMetadata
            AddressMetadata::set(
                $user,
                $detailAlamat,
                $kelurahan,
                $kecamatan,
                $kabupatenKota,
                $provinsi,
                $latitude,
                $longitude
            );

            // Sync kolom alamat (text) dengan metadata
            AddressMetadata::syncAlamatColumn($user);
        } catch (\Exception $e) {
            Log::error('Failed to set User alamat regional', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Get alamat lengkap dari metadata
    public static function getAlamatLengkap(User $user): string
    {
        return AddressMetadata::formatAddress($user, true);
    }

    // * Update alamat regional dari array data
    // * Kolom alamat akan otomatis di-sync oleh UserObserver saat save
    public static function updateAlamatRegional(User $user, array $data): void
    {
        try {
            $addressData = AddressMetadata::generate(
                $data['detail_alamat'] ?? '',
                $data['kelurahan'] ?? '',
                $data['kecamatan'] ?? '',
                $data['kabupaten_kota'] ?? '',
                $data['provinsi'] ?? '',
                isset($data['latitude']) ? (float) $data['latitude'] : null,
                isset($data['longitude']) ? (float) $data['longitude'] : null
            );

            // Update metadata saja, Observer akan auto-sync kolom alamat
            AddressMetadata::update($user, $addressData);
        } catch (\Exception $e) {
            Log::error('Failed to update User alamat regional', [
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil gaji pokok user
    public static function getGaji(User $user): ?int
    {
        return self::getMetadata($user, self::META_GAJI);
    }

    // * Set gaji pokok user
    public static function setGaji(User $user, int $gaji): void
    {
        self::setMetadata($user, self::META_GAJI, $gaji);
    }

    // * Create user baru dengan alamat regional dan GPS
    public static function createUser(array $data): User
    {
        try {
            // Generate metadata alamat dengan GPS
            $addressMetadata = AddressMetadata::generate(
                $data['detail_alamat'] ?? '',
                $data['kelurahan'] ?? '',
                $data['kecamatan'] ?? '',
                $data['kabupaten_kota'] ?? '',
                $data['provinsi'] ?? '',
                isset($data['latitude']) ? (float) $data['latitude'] : null,
                isset($data['longitude']) ? (float) $data['longitude'] : null
            );

            // Build alamat lengkap untuk kolom alamat (text)
            $alamatLengkap = AddressMetadata::buildAlamatFromArray($data);

            // Prepare metadata (merge alamat)
            $metadata = $addressMetadata;

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'password' => Hash::make($data['password']),
                'alamat' => $alamatLengkap,
                'avatar_url' => $data['avatar_url'] ?? null,
                'super_admin' => $data['super_admin'] ?? false,
                'metadata' => $metadata,
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Update profil user (nama, email, avatar)
    public static function updateProfile(
        User $user,
        string $name,
        string $email,
        ?string $avatarPath = null
    ): User {
        try {
            $changes = [
                'name_changed' => $name !== $user->name,
                'email_changed' => $email !== $user->email,
                'avatar_changed' => $avatarPath !== null && $avatarPath !== $user->avatar_url,
            ];

            $user->update([
                'name' => $name,
                'email' => $email,
                'avatar_url' => $avatarPath ?? $user->avatar_url,
            ]);

            Log::info('UserHelper: Profile updated successfully', [
                'user_id' => $user->id,
                'changes' => $changes,
            ]);

            return $user->fresh();
        } catch (\Exception $e) {
            Log::error('UserHelper: Failed to update profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Update password user dengan validasi current password
    public static function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        try {
            // Validasi current password
            if (!Hash::check($currentPassword, $user->password)) {
                Log::warning('UserHelper: Wrong current password attempt', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return false;
            }

            // Update password
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            Log::info('UserHelper: Password updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('UserHelper: Failed to update password', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_SHIFT => 'nullable|string|max:50',
            self::META_GAJI => 'nullable|integer|min:0',
        ];
    }
}

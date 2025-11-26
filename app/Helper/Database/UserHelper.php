<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola data User
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom alamat: alamat, detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude
// ? Kolom kepegawaian: gaji, jam_masuk, jam_keluar
// ? Kolom bank: bank_name, bank_account_number, bank_account_name

class UserHelper
{
    // * Password validation constants
    public const PASSWORD_MIN_LENGTH = 8;

    public const PASSWORD_MAX_LENGTH = 255;

    // * Avatar upload constants (in KB)
    public const AVATAR_MAX_SIZE_KB = 2048; // 2 MB

    // * Ambil alamat lengkap formatted
    public static function getAlamatLengkap(User $user): string
    {
        $parts = array_filter([
            $user->detail_alamat,
            $user->kelurahan ? "Kel. {$user->kelurahan}" : null,
            $user->kecamatan ? "Kec. {$user->kecamatan}" : null,
            $user->kabupaten_kota,
            $user->provinsi,
        ]);

        return implode(', ', $parts);
    }

    // * Set alamat regional
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
        $user->alamat = implode(', ', array_filter([
            $detailAlamat,
            $kelurahan ? "Kel. {$kelurahan}" : null,
            $kecamatan ? "Kec. {$kecamatan}" : null,
            $kabupatenKota,
            $provinsi,
        ]));
        $user->detail_alamat = $detailAlamat;
        $user->kelurahan = $kelurahan;
        $user->kecamatan = $kecamatan;
        $user->kabupaten_kota = $kabupatenKota;
        $user->provinsi = $provinsi;
        $user->latitude = $latitude;
        $user->longitude = $longitude;
    }

    // * Ambil gaji pokok user
    public static function getGaji(User $user): ?int
    {
        return $user->gaji;
    }

    // * Set gaji pokok user
    public static function setGaji(User $user, ?int $gaji): void
    {
        $user->gaji = $gaji;
    }

    // * Ambil jam masuk kerja
    public static function getJamMasuk(User $user): ?string
    {
        return $user->jam_masuk?->format('H:i');
    }

    // * Set jam masuk kerja
    public static function setJamMasuk(User $user, ?string $jamMasuk): void
    {
        $user->jam_masuk = $jamMasuk;
    }

    // * Ambil jam keluar kerja
    public static function getJamKeluar(User $user): ?string
    {
        return $user->jam_keluar?->format('H:i');
    }

    // * Set jam keluar kerja
    public static function setJamKeluar(User $user, ?string $jamKeluar): void
    {
        $user->jam_keluar = $jamKeluar;
    }

    // * Ambil informasi rekening bank user
    public static function getBankInfo(User $user): ?array
    {
        if ($user->bank_name || $user->bank_account_number) {
            return [
                'bank_name' => $user->bank_name,
                'account_number' => $user->bank_account_number,
                'account_name' => $user->bank_account_name,
            ];
        }

        return null;
    }

    // * Set informasi rekening bank user
    public static function setBankInfo(User $user, string $bankName, string $accountNumber, string $accountName): void
    {
        $user->bank_name = $bankName;
        $user->bank_account_number = $accountNumber;
        $user->bank_account_name = $accountName;
    }

    // * Create user baru dengan alamat regional dan GPS
    public static function createUser(array $data): User
    {
        try {
            // Build alamat lengkap
            $alamatLengkap = implode(', ', array_filter([
                $data['detail_alamat'] ?? '',
                ($data['kelurahan'] ?? null) ? "Kel. {$data['kelurahan']}" : null,
                ($data['kecamatan'] ?? null) ? "Kec. {$data['kecamatan']}" : null,
                $data['kabupaten_kota'] ?? '',
                $data['provinsi'] ?? '',
            ]));

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'password' => Hash::make($data['password']),
                'alamat' => $alamatLengkap,
                'detail_alamat' => $data['detail_alamat'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kabupaten_kota' => $data['kabupaten_kota'] ?? 'Kota Kendari',
                'provinsi' => $data['provinsi'] ?? 'Sulawesi Tenggara',
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'super_admin' => $data['super_admin'] ?? false,
                'gaji' => $data['gaji'] ?? null,
                'jam_masuk' => $data['jam_masuk'] ?? null,
                'jam_keluar' => $data['jam_keluar'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
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
            if (! Hash::check($currentPassword, $user->password)) {
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

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'detail_alamat' => 'nullable|string',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gaji' => 'nullable|integer|min:0',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get user options untuk dropdown (staf aktif)
     */
    public static function getUserOptions(): array
    {
        try {
            return User::where('super_admin', false)
                ->orderBy('name')
                ->get()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name.' - '.$user->email,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get user options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Helper\PhoneNumber;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola data Pelanggan
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom alamat: alamat, detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude
// ? Kolom loyalty: loyalty_points, member_card, avatar_url
// ? Kolom referral: direferensikan_oleh

class PelangganHelper
{
    // * Password validation constants
    public const PASSWORD_MIN_LENGTH = 8;

    public const PASSWORD_MAX_LENGTH = 255;

    // * Avatar upload constants (in KB)
    public const AVATAR_MAX_SIZE_KB = 2048; // 2 MB

    // * Ambil alamat lengkap formatted
    public static function getAlamatLengkap(Pelanggan $pelanggan): string
    {
        $parts = array_filter([
            $pelanggan->detail_alamat,
            $pelanggan->kelurahan ? "Kel. {$pelanggan->kelurahan}" : null,
            $pelanggan->kecamatan ? "Kec. {$pelanggan->kecamatan}" : null,
            $pelanggan->kabupaten_kota,
            $pelanggan->provinsi,
        ]);

        return implode(', ', $parts);
    }

    // * Set alamat regional
    public static function setAlamatRegional(
        Pelanggan $pelanggan,
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): void {
        $pelanggan->alamat = implode(', ', array_filter([
            $detailAlamat,
            $kelurahan ? "Kel. {$kelurahan}" : null,
            $kecamatan ? "Kec. {$kecamatan}" : null,
            $kabupatenKota,
            $provinsi,
        ]));
        $pelanggan->detail_alamat = $detailAlamat;
        $pelanggan->kelurahan = $kelurahan;
        $pelanggan->kecamatan = $kecamatan;
        $pelanggan->kabupaten_kota = $kabupatenKota;
        $pelanggan->provinsi = $provinsi;
        $pelanggan->latitude = $latitude;
        $pelanggan->longitude = $longitude;
    }

    // * Ambil total poin loyalty pelanggan
    public static function getLoyaltyPoints(Pelanggan $pelanggan): int
    {
        return $pelanggan->loyalty_points ?? 0;
    }

    // * Set total poin loyalty pelanggan
    public static function setLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        $pelanggan->loyalty_points = $points;
    }

    // * Tambah poin loyalty (reward, cashback, dll)
    public static function addLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        try {
            $pelanggan->increment('loyalty_points', $points);
        } catch (\Exception $e) {
            Log::error('Failed to add Pelanggan loyalty points', [
                'pelanggan_id' => $pelanggan->id,
                'points' => $points,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Generate kode pelanggan unik
    public static function generateKodePelanggan(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_pelanggan', 'PLG');
            $prefixLength = strlen($prefix);

            $lastPelanggan = Pelanggan::withTrashed()->orderBy('kode_pelanggan', 'desc')->first();

            if (! $lastPelanggan) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Pelanggan::where('kode_pelanggan', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Create pelanggan baru dengan address dan GPS
    public static function createPelanggan(array $data, ?string $tanggalDaftar = null): Pelanggan
    {
        try {
            // Normalize nomor HP (optional untuk Google OAuth)
            $normalizedPhone = null;
            if (! empty($data['no_hp'])) {
                $normalizedPhone = PhoneNumber::normalize($data['no_hp']);
                if (! $normalizedPhone) {
                    throw new \Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }
            }

            // Build alamat lengkap
            $alamatLengkap = implode(', ', array_filter([
                $data['detail_alamat'] ?? '',
                ($data['kelurahan'] ?? null) ? "Kel. {$data['kelurahan']}" : null,
                ($data['kecamatan'] ?? null) ? "Kec. {$data['kecamatan']}" : null,
                $data['kabupaten_kota'] ?? 'Kota Kendari',
                $data['provinsi'] ?? 'Sulawesi Tenggara',
            ]));

            // Create pelanggan
            return Pelanggan::create([
                'kode_pelanggan' => self::generateKodePelanggan(),
                'nama' => $data['nama'],
                'no_hp' => $normalizedPhone,
                'email' => $data['email'] ?? null,
                'alamat' => $alamatLengkap,
                'detail_alamat' => $data['detail_alamat'] ?? null,
                'kelurahan' => $data['kelurahan'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kabupaten_kota' => $data['kabupaten_kota'] ?? 'Kota Kendari',
                'provinsi' => $data['provinsi'] ?? 'Sulawesi Tenggara',
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
                'tanggal_daftar' => $tanggalDaftar ?? now(),
                'status' => 'Aktif',
                'loyalty_points' => 0,
                'member_card' => $data['member_card'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'direferensikan_oleh' => $data['direferensikan_oleh'] ?? null,
                'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create pelanggan', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Get pelanggan options untuk dropdown (with search)
    public static function getPelangganOptions(string $search = '', int $limit = 10): array
    {
        try {
            return Pelanggan::where('status', 'Aktif')
                ->where(function ($query) use ($search) {
                    if (! empty($search)) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_hp', 'like', "%{$search}%");
                    }
                })
                ->take($limit)
                ->orderBy('nama')
                ->get()
                ->map(fn ($p) => [
                    'id' => (string) $p->id,
                    'nama' => $p->nama,
                    'no_hp' => PhoneNumber::formatLocal($p->no_hp) ?? $p->no_hp,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get pelanggan options', [
                'search' => $search,
                'limit' => $limit,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Validate password strength
    public static function validatePassword(string $password): bool
    {
        $length = strlen($password);

        // Check minimum length
        if ($length < self::PASSWORD_MIN_LENGTH) {
            return false;
        }

        // Check maximum length
        if ($length > self::PASSWORD_MAX_LENGTH) {
            return false;
        }

        return true;
    }

    // * Generate validation rules untuk password
    public static function passwordRules(bool $required = false): string
    {
        $rules = [];

        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        $rules[] = 'string';
        $rules[] = 'min:'.self::PASSWORD_MIN_LENGTH;
        $rules[] = 'max:'.self::PASSWORD_MAX_LENGTH;

        return implode('|', $rules);
    }

    // * Get password requirements message untuk user
    public static function getPasswordRequirementsMessage(): string
    {
        return sprintf(
            'Password harus memiliki minimal %d karakter',
            self::PASSWORD_MIN_LENGTH
        );
    }

    // * Set password untuk pelanggan (with hashing)
    public static function setPassword(Pelanggan $pelanggan, string $password): void
    {
        try {
            if (! self::validatePassword($password)) {
                throw new \InvalidArgumentException(self::getPasswordRequirementsMessage());
            }

            $pelanggan->password = Hash::make($password);
        } catch (\InvalidArgumentException $e) {
            // Re-throw validation exceptions without logging (expected behavior)
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to set Pelanggan password', [
                'pelanggan_id' => $pelanggan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Check apakah pelanggan memiliki password (sudah terdaftar di app)
    public static function hasPassword(Pelanggan $pelanggan): bool
    {
        return ! empty($pelanggan->password);
    }

    // * Verify password pelanggan
    public static function verifyPassword(Pelanggan $pelanggan, string $password): bool
    {
        if (! self::hasPassword($pelanggan)) {
            return false;
        }

        return Hash::check($password, $pelanggan->password);
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
            'loyalty_points' => 'nullable|integer|min:0',
            'member_card' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|string|max:255',
        ];
    }
}

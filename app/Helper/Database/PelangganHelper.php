<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Helper\AddressMetadata;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola data Pelanggan
//
// ? Metadata yang disimpan:
// * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
// * - kelurahan: Kelurahan/Desa
// * - kecamatan: Kecamatan
// * - kabupaten_kota: Kabupaten/Kota
// * - provinsi: Provinsi
// * - latitude: Koordinat GPS latitude
// * - longitude: Koordinat GPS longitude
// * - member_card: Nomor kartu member
// * - loyalty_points: Poin loyalty pelanggan
// * - preferensi_pengiriman: Pilihan antar_jemput atau ambil_sendiri

class PelangganHelper
{
    // * Password validation constants
    public const PASSWORD_MIN_LENGTH = 8;

    public const PASSWORD_MAX_LENGTH = 255;

    // * Avatar upload constants (in KB)
    public const AVATAR_MAX_SIZE_KB = 2048; // 2 MB

    // * Metadata constants
    public const META_MEMBER_CARD = 'member_card';

    public const META_LOYALTY_POINTS = 'loyalty_points';

    public const META_PREFERENSI_PENGIRIMAN = 'preferensi_pengiriman';

    public const META_AVATAR_URL = 'avatar_url';

    // * Ambil nilai dari metadata
    public static function getMetadata(Pelanggan $pelanggan, string $key, mixed $default = null): mixed
    {
        return data_get($pelanggan->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Pelanggan $pelanggan, string $key, mixed $value): void
    {
        $metadata = $pelanggan->metadata ?? [];
        data_set($metadata, $key, $value);
        $pelanggan->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Pelanggan $pelanggan, array $data): void
    {
        $pelanggan->metadata = array_merge($pelanggan->metadata ?? [], $data);
    }

    // * Set alamat regional ke metadata
    // * Kolom alamat akan otomatis di-sync oleh PelangganObserver saat save
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
        try {
            // Set metadata alamat menggunakan AddressMetadata
            // Kolom alamat akan otomatis di-sync oleh PelangganObserver saat save
            AddressMetadata::set(
                $pelanggan,
                $detailAlamat,
                $kelurahan,
                $kecamatan,
                $kabupatenKota,
                $provinsi,
                $latitude,
                $longitude
            );
        } catch (\Exception $e) {
            Log::error('Failed to set Pelanggan alamat regional', [
                'pelanggan_id' => $pelanggan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Get alamat lengkap dari metadata
    public static function getAlamatLengkap(Pelanggan $pelanggan): string
    {
        return AddressMetadata::formatAddress($pelanggan, true);
    }

    // * Update alamat regional dari array data
    // * Kolom alamat akan otomatis di-sync oleh PelangganObserver saat save
    public static function updateAlamatRegional(Pelanggan $pelanggan, array $data): void
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
            AddressMetadata::update($pelanggan, $addressData);
        } catch (\Exception $e) {
            Log::error('Failed to update Pelanggan alamat regional', [
                'pelanggan_id' => $pelanggan->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil total poin loyalty pelanggan
    public static function getLoyaltyPoints(Pelanggan $pelanggan): int
    {
        return (int) self::getMetadata($pelanggan, self::META_LOYALTY_POINTS, 0);
    }

    // * Set total poin loyalty pelanggan
    public static function setLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        self::setMetadata($pelanggan, self::META_LOYALTY_POINTS, $points);
    }

    // * Tambah poin loyalty (reward, cashback, dll)
    public static function addLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        try {
            $current = self::getLoyaltyPoints($pelanggan);
            self::setLoyaltyPoints($pelanggan, $current + $points);
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

    // * Ambil avatar URL dari metadata
    public static function getAvatarUrl(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_AVATAR_URL);
    }

    // * Set avatar URL ke metadata
    public static function setAvatarUrl(Pelanggan $pelanggan, ?string $avatarUrl): void
    {
        self::setMetadata($pelanggan, self::META_AVATAR_URL, $avatarUrl);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_MEMBER_CARD => 'nullable|string|max:50',
            self::META_LOYALTY_POINTS => 'nullable|integer|min:0',
            self::META_PREFERENSI_PENGIRIMAN => 'nullable|in:antar_jemput,ambil_sendiri',
            self::META_AVATAR_URL => 'nullable|string|max:255',
        ];
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

    // * Create pelanggan baru dengan address metadata dan GPS
    public static function createPelanggan(array $data, ?string $tanggalDaftar = null): Pelanggan
    {
        try {
            // Normalize nomor HP
            $normalizedPhone = PhoneNumber::normalize($data['no_hp'] ?? '');
            if (! $normalizedPhone) {
                throw new \Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
            }

            // Generate metadata alamat dengan GPS
            $addressMetadata = AddressMetadata::generate(
                $data['detail_alamat'] ?? '',
                $data['kelurahan'] ?? '',
                $data['kecamatan'] ?? '',
                $data['kabupaten_kota'] ?? RegionalLocation::getRegencyName(),
                $data['provinsi'] ?? RegionalLocation::getProvinceName(),
                isset($data['latitude']) ? (float) $data['latitude'] : null,
                isset($data['longitude']) ? (float) $data['longitude'] : null
            );

            // Alamat lengkap gabungan untuk kolom alamat (text)
            $alamatLengkap = AddressMetadata::buildAlamatFromArray($data);

            // Create pelanggan dengan metadata saja (tidak ada kolom regional terpisah)
            return Pelanggan::create([
                'kode_pelanggan' => self::generateKodePelanggan(),
                'nama' => $data['nama'],
                'no_hp' => $normalizedPhone,
                'email' => $data['email'] ?? null,
                'alamat' => $alamatLengkap,
                'tanggal_daftar' => $tanggalDaftar ?? now(),
                'total_transaksi' => 0,
                'status' => 'Aktif',
                'metadata' => $addressMetadata,
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
}

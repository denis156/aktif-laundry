<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Helper\PhoneNumber;
use App\Models\Kurir;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola data Kurir
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom alamat: alamat, detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude
// ? Kolom lain: avatar_url, bank_name, bank_account_number, bank_account_name
// ? Kolom emergency: emergency_contact_name, emergency_contact_phone, emergency_contact_relation

class KurirHelper
{
    // * Password validation constants
    public const PASSWORD_MIN_LENGTH = 8;

    public const PASSWORD_MAX_LENGTH = 255;

    // * Avatar upload constants (in KB)
    public const AVATAR_MAX_SIZE_KB = 2048; // 2 MB

    // * Status constants
    public const STATUS_AKTIF = 'Aktif';

    public const STATUS_TIDAK_AKTIF = 'Tidak Aktif';

    public const STATUS_CUTI = 'Cuti';

    // * Jenis Kendaraan constants
    public const JENIS_KENDARAAN_MOTOR = 'Motor';

    public const JENIS_KENDARAAN_MOBIL = 'Mobil';

    // * Ambil alamat lengkap formatted
    public static function getAlamatLengkap(Kurir $kurir): string
    {
        $parts = array_filter([
            $kurir->detail_alamat,
            $kurir->kelurahan ? "Kel. {$kurir->kelurahan}" : null,
            $kurir->kecamatan ? "Kec. {$kurir->kecamatan}" : null,
            $kurir->kabupaten_kota,
            $kurir->provinsi,
        ]);

        return implode(', ', $parts);
    }

    // * Set alamat regional
    public static function setAlamatRegional(
        Kurir $kurir,
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): void {
        $kurir->alamat = implode(', ', array_filter([
            $detailAlamat,
            $kelurahan ? "Kel. {$kelurahan}" : null,
            $kecamatan ? "Kec. {$kecamatan}" : null,
            $kabupatenKota,
            $provinsi,
        ]));
        $kurir->detail_alamat = $detailAlamat;
        $kurir->kelurahan = $kelurahan;
        $kurir->kecamatan = $kecamatan;
        $kurir->kabupaten_kota = $kabupatenKota;
        $kurir->provinsi = $provinsi;
        $kurir->latitude = $latitude;
        $kurir->longitude = $longitude;
    }

    // * Ambil informasi rekening bank kurir
    public static function getBankInfo(Kurir $kurir): ?array
    {
        if ($kurir->bank_name || $kurir->bank_account_number) {
            return [
                'bank_name' => $kurir->bank_name,
                'account_number' => $kurir->bank_account_number,
                'account_name' => $kurir->bank_account_name,
            ];
        }

        return null;
    }

    // * Set informasi rekening bank kurir
    public static function setBankInfo(Kurir $kurir, string $bankName, string $accountNumber, string $accountName): void
    {
        $kurir->bank_name = $bankName;
        $kurir->bank_account_number = $accountNumber;
        $kurir->bank_account_name = $accountName;
    }

    // * Ambil kontak darurat kurir
    public static function getEmergencyContact(Kurir $kurir): ?array
    {
        if ($kurir->emergency_contact_name || $kurir->emergency_contact_phone) {
            return [
                'name' => $kurir->emergency_contact_name,
                'phone' => $kurir->emergency_contact_phone,
                'relation' => $kurir->emergency_contact_relation,
            ];
        }

        return null;
    }

    // * Set kontak darurat kurir
    public static function setEmergencyContact(Kurir $kurir, string $name, string $phone, ?string $relation = null): void
    {
        $kurir->emergency_contact_name = $name;
        $kurir->emergency_contact_phone = $phone;
        $kurir->emergency_contact_relation = $relation;
    }

    // * Generate kode kurir unik
    public static function generateKodeKurir(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_kurir', 'KUR');
            $prefixLength = strlen($prefix);

            $lastKurir = Kurir::withTrashed()->orderBy('kode_kurir', 'desc')->first();

            if (! $lastKurir) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastKurir->kode_kurir, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Kurir::where('kode_kurir', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode kurir', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Create kurir baru dengan alamat regional dan GPS
    public static function createKurir(array $data): Kurir
    {
        try {
            // Normalize nomor HP
            $normalizedPhone = PhoneNumber::normalize($data['no_hp'] ?? '');
            if (! $normalizedPhone) {
                throw new \Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
            }

            // Build alamat lengkap
            $alamatLengkap = implode(', ', array_filter([
                $data['detail_alamat'] ?? '',
                ($data['kelurahan'] ?? null) ? "Kel. {$data['kelurahan']}" : null,
                ($data['kecamatan'] ?? null) ? "Kec. {$data['kecamatan']}" : null,
                $data['kabupaten_kota'] ?? '',
                $data['provinsi'] ?? '',
            ]));

            // Create kurir
            $kurir = Kurir::create([
                'kode_kurir' => self::generateKodeKurir(),
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
                'no_kendaraan' => $data['no_kendaraan'] ?? null,
                'jenis_kendaraan' => $data['jenis_kendaraan'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'tanggal_bergabung' => $data['tanggal_bergabung'] ?? now(),
                'status' => 'Aktif',
                'password' => isset($data['password']) ? Hash::make($data['password']) : Hash::make('password'),
            ]);

            return $kurir;
        } catch (\Exception $e) {
            Log::error('Failed to create kurir', [
                'data' => $data,
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
    public static function passwordRules(bool $required = true): string
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
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:15',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get kurir options untuk dropdown (hanya yang aktif)
     */
    public static function getKurirOptions(): array
    {
        try {
            return Kurir::where('status', 'Aktif')
                ->orderBy('nama')
                ->get()
                ->map(fn ($kurir) => [
                    'id' => $kurir->id,
                    'name' => $kurir->nama.' - '.$kurir->no_hp,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get kurir options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get status options untuk dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            ['id' => self::STATUS_AKTIF, 'name' => 'Aktif'],
            ['id' => self::STATUS_TIDAK_AKTIF, 'name' => 'Tidak Aktif'],
            ['id' => self::STATUS_CUTI, 'name' => 'Cuti'],
        ];
    }

    /**
     * Get jenis kendaraan options untuk dropdown
     */
    public static function getJenisKendaraanOptions(): array
    {
        return [
            ['id' => self::JENIS_KENDARAAN_MOTOR, 'name' => 'Motor'],
            ['id' => self::JENIS_KENDARAAN_MOBIL, 'name' => 'Mobil'],
        ];
    }

    /**
     * Check apakah status valid
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, [
            self::STATUS_AKTIF,
            self::STATUS_TIDAK_AKTIF,
            self::STATUS_CUTI,
        ], true);
    }

    /**
     * Check apakah jenis kendaraan valid
     */
    public static function isValidJenisKendaraan(?string $jenisKendaraan): bool
    {
        if ($jenisKendaraan === null) {
            return true; // Nullable
        }

        return in_array($jenisKendaraan, [
            self::JENIS_KENDARAAN_MOTOR,
            self::JENIS_KENDARAAN_MOBIL,
        ], true);
    }
}

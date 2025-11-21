<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// ! Helper untuk menangani data wilayah Indonesia
//
// ? Scope: Kota Kendari, Sulawesi Tenggara
// ? Menggunakan API wilayah.id untuk data wilayah yang up-to-date
//
// * Features:
// * - Get provinces (provinsi)
// * - Get regencies by province (kabupaten/kota)
// * - Get districts by regency (kecamatan)
// * - Get villages by district (kelurahan/desa)
// * - Cache support (30 hari)
// * - Format alamat lengkap

class RegionalLocation
{
    // ! Base URL API wilayah.id
    private const BASE_URL = 'https://wilayah.id/api';

    // ! Cache duration (30 hari)
    private const CACHE_DURATION_DAYS = 30;

    // ! HTTP timeout (10 detik)
    private const HTTP_TIMEOUT = 10;

    // ! Sulawesi Tenggara province code
    private const SULAWESI_TENGGARA_CODE = '74';

    // ! Kota Kendari regency code
    private const KOTA_KENDARI_CODE = '74.71';

    // * Get semua provinsi di Indonesia
    public static function getProvinces(): array
    {
        return Cache::remember('regional_provinces', now()->addDays(self::CACHE_DURATION_DAYS), function () {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->get(self::BASE_URL . '/provinces.json');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                logger()->error('Failed to fetch provinces', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    // * Get semua kecamatan di Kota Kendari
    public static function getKendariDistricts(): array
    {
        return self::getDistrictsByRegency(self::KOTA_KENDARI_CODE);
    }

    // * Get kabupaten/kota berdasarkan kode provinsi
    public static function getRegenciesByProvince(string $provinceCode): array
    {
        return Cache::remember('regional_regencies_' . $provinceCode, now()->addDays(self::CACHE_DURATION_DAYS), function () use ($provinceCode) {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->get(self::BASE_URL . '/regencies/' . $provinceCode . '.json');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                logger()->error('Failed to fetch regencies', [
                    'province_code' => $provinceCode,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    // * Get kecamatan berdasarkan kode kabupaten/kota
    public static function getDistrictsByRegency(string $regencyCode): array
    {
        return Cache::remember('regional_districts_' . $regencyCode, now()->addDays(self::CACHE_DURATION_DAYS), function () use ($regencyCode) {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->get(self::BASE_URL . '/districts/' . $regencyCode . '.json');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                logger()->error('Failed to fetch districts', [
                    'regency_code' => $regencyCode,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    // * Get kelurahan/desa berdasarkan kode kecamatan
    public static function getVillagesByDistrict(string $districtCode): array
    {
        return Cache::remember('regional_villages_' . $districtCode, now()->addDays(self::CACHE_DURATION_DAYS), function () use ($districtCode) {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->get(self::BASE_URL . '/villages/' . $districtCode . '.json');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                logger()->error('Failed to fetch villages', [
                    'district_code' => $districtCode,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    // ? Cari provinsi berdasarkan nama
    public static function findProvinceByName(string $name): ?array
    {
        $provinces = self::getProvinces();

        foreach ($provinces as $province) {
            if (stripos($province['name'] ?? '', $name) !== false) {
                return $province;
            }
        }

        return null;
    }

    // ? Cari kabupaten/kota berdasarkan nama dalam provinsi tertentu
    public static function findRegencyByName(string $provinceCode, string $name): ?array
    {
        $regencies = self::getRegenciesByProvince($provinceCode);

        foreach ($regencies as $regency) {
            if (stripos($regency['name'] ?? '', $name) !== false) {
                return $regency;
            }
        }

        return null;
    }

    // * Get nama provinsi (Sulawesi Tenggara)
    public static function getProvinceName(): string
    {
        return 'Sulawesi Tenggara';
    }

    // * Get nama kabupaten/kota (Kota Kendari)
    public static function getRegencyName(): string
    {
        return 'Kota Kendari';
    }

    // * Format alamat lengkap dari komponen wilayah
    public static function formatFullAddress(
        string $detailAddress,
        string $villageName,
        string $districtName,
        ?string $regencyName = null,
        ?string $provinceName = null
    ): string {
        $parts = array_filter([
            $detailAddress,
            $villageName,
            $districtName,
            $regencyName ?? self::getRegencyName(),
            $provinceName ?? self::getProvinceName(),
        ]);

        return implode(', ', $parts);
    }

    // * Format alamat singkat (tanpa provinsi)
    public static function formatShortAddress(
        string $detailAddress,
        string $villageName,
        string $districtName,
        ?string $regencyName = null
    ): string {
        $parts = array_filter([
            $detailAddress,
            $villageName,
            $districtName,
            $regencyName ?? self::getRegencyName(),
        ]);

        return implode(', ', $parts);
    }

    // ! Clear cache untuk wilayah tertentu
    public static function clearCache(?string $key = null): bool
    {
        if ($key) {
            return Cache::forget($key);
        }

        // Clear semua cache wilayah
        $patterns = [
            'regional_provinces',
            'regional_regencies_*',
            'regional_districts_*',
            'regional_villages_*',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // Untuk pattern dengan wildcard, perlu implementasi khusus tergantung cache driver
                // Contoh sederhana: hapus yang umum digunakan
                continue;
            }
            Cache::forget($pattern);
        }

        return true;
    }

    // * Get URL API untuk endpoint tertentu
    public static function getApiUrl(string $endpoint): string
    {
        return self::BASE_URL . '/' . ltrim($endpoint, '/');
    }

    // ? Validate apakah kode wilayah valid (format check)
    public static function validateCode(string $code, string $type): bool
    {
        switch ($type) {
            case 'province':
                // Format: 2 digit (contoh: 74)
                return preg_match('/^\d{2}$/', $code) === 1;

            case 'regency':
                // Format: 2.2 digit (contoh: 74.71)
                return preg_match('/^\d{2}\.\d{2}$/', $code) === 1;

            case 'district':
                // Format: 2.2.2 digit (contoh: 74.71.01)
                return preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $code) === 1;

            case 'village':
                // Format: 2.2.2.4 digit (contoh: 74.71.01.1001)
                return preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $code) === 1;

            default:
                return false;
        }
    }

    // * Get options untuk select provinsi (format untuk Mary UI)
    // Fixed ke Sulawesi Tenggara saja
    public static function getProvinceOptions(): array
    {
        return [
            [
                'id' => self::getProvinceName(),
                'name' => self::getProvinceName(),
            ],
        ];
    }

    // * Get options untuk select kabupaten/kota di Sulawesi Tenggara (format untuk Mary UI)
    public static function getRegencyOptions(?string $provinceName = null): array
    {
        // Hanya Sulawesi Tenggara yang didukung
        $regencies = self::getRegenciesByProvince(self::SULAWESI_TENGGARA_CODE);
        $options = [];

        foreach ($regencies as $regency) {
            $options[] = [
                'id' => $regency['name'] ?? '',
                'name' => $regency['name'] ?? '',
            ];
        }

        return $options;
    }

    // * Get options untuk select kecamatan berdasarkan nama kabupaten/kota (format untuk Mary UI)
    public static function getDistrictOptions(string $regencyName): array
    {
        // Search regency code di Sulawesi Tenggara
        $regencies = self::getRegenciesByProvince(self::SULAWESI_TENGGARA_CODE);

        foreach ($regencies as $regency) {
            if (($regency['name'] ?? '') === $regencyName) {
                $districts = self::getDistrictsByRegency($regency['code']);
                $options = [];

                foreach ($districts as $district) {
                    $options[] = [
                        'id' => $district['name'] ?? '',
                        'name' => $district['name'] ?? '',
                    ];
                }

                return $options;
            }
        }

        return [];
    }

    // * Get options untuk select kelurahan berdasarkan nama kecamatan (format untuk Mary UI)
    public static function getVillageOptions(string $districtName): array
    {
        // Search district code di Sulawesi Tenggara
        $regencies = self::getRegenciesByProvince(self::SULAWESI_TENGGARA_CODE);

        foreach ($regencies as $regency) {
            $districts = self::getDistrictsByRegency($regency['code']);

            foreach ($districts as $district) {
                if (($district['name'] ?? '') === $districtName) {
                    $villages = self::getVillagesByDistrict($district['code']);
                    $options = [];

                    foreach ($villages as $village) {
                        $options[] = [
                            'id' => $village['name'] ?? '',
                            'name' => $village['name'] ?? '',
                        ];
                    }

                    return $options;
                }
            }
        }

        return [];
    }
}

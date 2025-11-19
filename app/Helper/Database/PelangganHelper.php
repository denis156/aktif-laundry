<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\Pelanggan;

// ! Helper untuk mengelola metadata Pelanggan
//
// ? Metadata yang didukung:
// * - member_card: Nomor kartu member
// * - loyalty_points: Poin loyalty pelanggan
// * - preferensi_pengiriman: Pilihan antar_jemput atau ambil_sendiri
// * - villages: Kelurahan/Desa
// * - district: Kecamatan
// * - regency: Kabupaten/Kota
// * - province: Provinsi
// * - detail_alamat: Detail alamat lengkap

class PelangganHelper
{
    public const META_MEMBER_CARD = 'member_card';
    public const META_LOYALTY_POINTS = 'loyalty_points';
    public const META_PREFERENSI_PENGIRIMAN = 'preferensi_pengiriman';
    public const META_VILLAGES = 'villages';
    public const META_DISTRICT = 'district';
    public const META_REGENCY = 'regency';
    public const META_PROVINCE = 'province';
    public const META_DETAIL_ALAMAT = 'detail_alamat';

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
        $current = self::getLoyaltyPoints($pelanggan);
        self::setLoyaltyPoints($pelanggan, $current + $points);
    }

    // * Ambil kelurahan/desa pelanggan
    public static function getVillages(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_VILLAGES);
    }

    // * Set kelurahan/desa pelanggan
    public static function setVillages(Pelanggan $pelanggan, string $villages): void
    {
        self::setMetadata($pelanggan, self::META_VILLAGES, $villages);
    }

    // * Ambil kecamatan pelanggan
    public static function getDistrict(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_DISTRICT);
    }

    // * Set kecamatan pelanggan
    public static function setDistrict(Pelanggan $pelanggan, string $district): void
    {
        self::setMetadata($pelanggan, self::META_DISTRICT, $district);
    }

    // * Ambil kabupaten/kota pelanggan
    public static function getRegency(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_REGENCY);
    }

    // * Set kabupaten/kota pelanggan
    public static function setRegency(Pelanggan $pelanggan, string $regency): void
    {
        self::setMetadata($pelanggan, self::META_REGENCY, $regency);
    }

    // * Ambil provinsi pelanggan
    public static function getProvince(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_PROVINCE);
    }

    // * Set provinsi pelanggan
    public static function setProvince(Pelanggan $pelanggan, string $province): void
    {
        self::setMetadata($pelanggan, self::META_PROVINCE, $province);
    }

    // * Ambil detail alamat pelanggan
    public static function getDetailAlamat(Pelanggan $pelanggan): ?string
    {
        return self::getMetadata($pelanggan, self::META_DETAIL_ALAMAT);
    }

    // * Set detail alamat pelanggan
    public static function setDetailAlamat(Pelanggan $pelanggan, string $detailAlamat): void
    {
        self::setMetadata($pelanggan, self::META_DETAIL_ALAMAT, $detailAlamat);
    }

    // * Set alamat lengkap sekaligus (detail, kelurahan, kecamatan, kabupaten, provinsi)
    public static function setFullAddress(
        Pelanggan $pelanggan,
        string $detailAlamat,
        string $villages,
        string $district,
        string $regency,
        string $province
    ): void {
        self::mergeMetadata($pelanggan, [
            self::META_DETAIL_ALAMAT => $detailAlamat,
            self::META_VILLAGES => $villages,
            self::META_DISTRICT => $district,
            self::META_REGENCY => $regency,
            self::META_PROVINCE => $province,
        ]);
    }

    // * Gabungkan semua bagian alamat jadi satu string untuk display
    public static function getFullAddressString(Pelanggan $pelanggan): string
    {
        $parts = array_filter([
            self::getDetailAlamat($pelanggan),
            self::getVillages($pelanggan),
            self::getDistrict($pelanggan),
            self::getRegency($pelanggan),
            self::getProvince($pelanggan),
        ]);

        return implode(', ', $parts);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_MEMBER_CARD => 'nullable|string|max:50',
            self::META_LOYALTY_POINTS => 'nullable|integer|min:0',
            self::META_PREFERENSI_PENGIRIMAN => 'nullable|in:antar_jemput,ambil_sendiri',
            self::META_VILLAGES => 'nullable|string|max:100',
            self::META_DISTRICT => 'nullable|string|max:100',
            self::META_REGENCY => 'nullable|string|max:100',
            self::META_PROVINCE => 'nullable|string|max:100',
            self::META_DETAIL_ALAMAT => 'nullable|string',
        ];
    }
}

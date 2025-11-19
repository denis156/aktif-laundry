<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\Transaksi;

// ! Helper untuk mengelola metadata Transaksi
//
// ? Metadata yang didukung:
// * - pembayaran: Info pembayaran (metode, status, dll)
// * - promo: Data promo yang digunakan
// * - referral: Data referral yang digunakan
// * - foto_bukti_timbangan: Array path foto timbangan (multiple)
// * - foto_bukti_pembayaran: Array path foto bukti bayar (multiple)
// * - kurir_jemput: Nama kurir yang jemput
// * - kurir_antar: Nama kurir yang antar

class TransaksiHelper
{
    public const META_PEMBAYARAN = 'pembayaran';
    public const META_PROMO = 'promo';
    public const META_REFERRAL = 'referral';
    public const META_FOTO_BUKTI_TIMBANGAN = 'foto_bukti_timbangan';
    public const META_FOTO_BUKTI_PEMBAYARAN = 'foto_bukti_pembayaran';
    public const META_KURIR_JEMPUT = 'kurir_jemput';
    public const META_KURIR_ANTAR = 'kurir_antar';

    // * Ambil nilai dari metadata
    public static function getMetadata(Transaksi $transaksi, string $key, mixed $default = null): mixed
    {
        return data_get($transaksi->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Transaksi $transaksi, string $key, mixed $value): void
    {
        $metadata = $transaksi->metadata ?? [];
        data_set($metadata, $key, $value);
        $transaksi->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Transaksi $transaksi, array $data): void
    {
        $transaksi->metadata = array_merge($transaksi->metadata ?? [], $data);
    }

    // * Ambil informasi promo yang digunakan
    public static function getPromoInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_PROMO);
    }

    // * Set informasi promo yang digunakan
    public static function setPromoInfo(Transaksi $transaksi, array $promoData): void
    {
        self::setMetadata($transaksi, self::META_PROMO, $promoData);
    }

    // * Ambil informasi referral yang digunakan
    public static function getReferralInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_REFERRAL);
    }

    // * Set informasi referral yang digunakan
    public static function setReferralInfo(Transaksi $transaksi, array $referralData): void
    {
        self::setMetadata($transaksi, self::META_REFERRAL, $referralData);
    }

    // * Ambil nama kurir yang jemput cucian
    public static function getKurirJemput(Transaksi $transaksi): ?string
    {
        return self::getMetadata($transaksi, self::META_KURIR_JEMPUT);
    }

    // * Set nama kurir yang jemput cucian
    public static function setKurirJemput(Transaksi $transaksi, string $kurirName): void
    {
        self::setMetadata($transaksi, self::META_KURIR_JEMPUT, $kurirName);
    }

    // * Ambil nama kurir yang antar cucian
    public static function getKurirAntar(Transaksi $transaksi): ?string
    {
        return self::getMetadata($transaksi, self::META_KURIR_ANTAR);
    }

    // * Set nama kurir yang antar cucian
    public static function setKurirAntar(Transaksi $transaksi, string $kurirName): void
    {
        self::setMetadata($transaksi, self::META_KURIR_ANTAR, $kurirName);
    }

    // * Ambil informasi pembayaran transaksi
    public static function getPembayaranInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_PEMBAYARAN);
    }

    // * Set informasi pembayaran transaksi
    public static function setPembayaranInfo(Transaksi $transaksi, array $pembayaranData): void
    {
        self::setMetadata($transaksi, self::META_PEMBAYARAN, $pembayaranData);
    }

    // * Ambil semua foto bukti timbangan
    public static function getFotoBuktiTimbangan(Transaksi $transaksi): array
    {
        return (array) self::getMetadata($transaksi, self::META_FOTO_BUKTI_TIMBANGAN, []);
    }

    // * Tambah foto bukti timbangan ke array yang sudah ada
    public static function addFotoBuktiTimbangan(Transaksi $transaksi, string $fotoPath): void
    {
        $fotos = self::getFotoBuktiTimbangan($transaksi);
        $fotos[] = $fotoPath;
        self::setMetadata($transaksi, self::META_FOTO_BUKTI_TIMBANGAN, $fotos);
    }

    // * Ambil semua foto bukti pembayaran
    public static function getFotoBuktiPembayaran(Transaksi $transaksi): array
    {
        return (array) self::getMetadata($transaksi, self::META_FOTO_BUKTI_PEMBAYARAN, []);
    }

    // * Tambah foto bukti pembayaran ke array yang sudah ada
    public static function addFotoBuktiPembayaran(Transaksi $transaksi, string $fotoPath): void
    {
        $fotos = self::getFotoBuktiPembayaran($transaksi);
        $fotos[] = $fotoPath;
        self::setMetadata($transaksi, self::META_FOTO_BUKTI_PEMBAYARAN, $fotos);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_PEMBAYARAN => 'nullable|array',
            self::META_PROMO => 'nullable|array',
            self::META_REFERRAL => 'nullable|array',
            self::META_FOTO_BUKTI_TIMBANGAN => 'nullable|array',
            self::META_FOTO_BUKTI_PEMBAYARAN => 'nullable|array',
            self::META_KURIR_JEMPUT => 'nullable|string|max:100',
            self::META_KURIR_ANTAR => 'nullable|string|max:100',
        ];
    }
}

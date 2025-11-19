<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\Pembayaran;

// ! Helper untuk mengelola metadata Pembayaran
//
// ? Metadata yang didukung:
// * - bank_pengirim: Nama bank pengirim (untuk transfer)
// * - rekening_pengirim: Nomor rekening pengirim (untuk transfer)
// * - nama_pengirim: Nama pemilik rekening pengirim (untuk transfer)
// * - waktu_transfer: Waktu transfer dilakukan
// * - verified_by: User ID yang verifikasi
// * - verified_at: Waktu verifikasi

class PembayaranHelper
{
    public const META_BANK_PENGIRIM = 'bank_pengirim';
    public const META_REKENING_PENGIRIM = 'rekening_pengirim';
    public const META_NAMA_PENGIRIM = 'nama_pengirim';
    public const META_WAKTU_TRANSFER = 'waktu_transfer';
    public const META_VERIFIED_BY = 'verified_by';
    public const META_VERIFIED_AT = 'verified_at';

    // * Ambil nilai dari metadata
    public static function getMetadata(Pembayaran $pembayaran, string $key, mixed $default = null): mixed
    {
        return data_get($pembayaran->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Pembayaran $pembayaran, string $key, mixed $value): void
    {
        $metadata = $pembayaran->metadata ?? [];
        data_set($metadata, $key, $value);
        $pembayaran->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Pembayaran $pembayaran, array $data): void
    {
        $pembayaran->metadata = array_merge($pembayaran->metadata ?? [], $data);
    }

    // * Ambil nama bank pengirim
    public static function getBankPengirim(Pembayaran $pembayaran): ?string
    {
        return self::getMetadata($pembayaran, self::META_BANK_PENGIRIM);
    }

    // * Set nama bank pengirim
    public static function setBankPengirim(Pembayaran $pembayaran, string $bank): void
    {
        self::setMetadata($pembayaran, self::META_BANK_PENGIRIM, $bank);
    }

    // * Ambil nomor rekening pengirim
    public static function getRekeningPengirim(Pembayaran $pembayaran): ?string
    {
        return self::getMetadata($pembayaran, self::META_REKENING_PENGIRIM);
    }

    // * Set nomor rekening pengirim
    public static function setRekeningPengirim(Pembayaran $pembayaran, string $rekening): void
    {
        self::setMetadata($pembayaran, self::META_REKENING_PENGIRIM, $rekening);
    }

    // * Ambil nama pemilik rekening pengirim
    public static function getNamaPengirim(Pembayaran $pembayaran): ?string
    {
        return self::getMetadata($pembayaran, self::META_NAMA_PENGIRIM);
    }

    // * Set nama pemilik rekening pengirim
    public static function setNamaPengirim(Pembayaran $pembayaran, string $nama): void
    {
        self::setMetadata($pembayaran, self::META_NAMA_PENGIRIM, $nama);
    }

    // * Set info verifikasi sekaligus (user ID dan timestamp)
    public static function setVerificationInfo(Pembayaran $pembayaran, int $userId, string $timestamp): void
    {
        self::setMetadata($pembayaran, self::META_VERIFIED_BY, $userId);
        self::setMetadata($pembayaran, self::META_VERIFIED_AT, $timestamp);
    }

    // ? Ambil ID user yang verifikasi pembayaran
    public static function getVerifiedBy(Pembayaran $pembayaran): ?int
    {
        return self::getMetadata($pembayaran, self::META_VERIFIED_BY);
    }

    // ? Ambil waktu verifikasi pembayaran
    public static function getVerifiedAt(Pembayaran $pembayaran): ?string
    {
        return self::getMetadata($pembayaran, self::META_VERIFIED_AT);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_BANK_PENGIRIM => 'nullable|string|max:100',
            self::META_REKENING_PENGIRIM => 'nullable|string|max:50',
            self::META_NAMA_PENGIRIM => 'nullable|string|max:100',
            self::META_WAKTU_TRANSFER => 'nullable|date',
            self::META_VERIFIED_BY => 'nullable|integer',
            self::META_VERIFIED_AT => 'nullable|date',
        ];
    }
}

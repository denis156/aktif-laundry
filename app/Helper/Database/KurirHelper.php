<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Kurir;

// ! Helper untuk mengelola metadata Kurir
//
// ? Metadata yang didukung:
// * - area_coverage: Area yang bisa dilayani kurir (array)
// * - bank_info: Info rekening bank kurir
// * - emergency_contact: Kontak darurat kurir

class KurirHelper
{
    public const META_AREA_COVERAGE = 'area_coverage';
    public const META_BANK_INFO = 'bank_info';
    public const META_EMERGENCY_CONTACT = 'emergency_contact';

    // * Ambil nilai dari metadata
    public static function getMetadata(Kurir $kurir, string $key, mixed $default = null): mixed
    {
        return data_get($kurir->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Kurir $kurir, string $key, mixed $value): void
    {
        $metadata = $kurir->metadata ?? [];
        data_set($metadata, $key, $value);
        $kurir->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Kurir $kurir, array $data): void
    {
        $kurir->metadata = array_merge($kurir->metadata ?? [], $data);
    }

    // * Ambil daftar area yang bisa dilayani kurir
    public static function getAreaCoverage(Kurir $kurir): array
    {
        return (array) self::getMetadata($kurir, self::META_AREA_COVERAGE, []);
    }

    // * Set daftar area yang bisa dilayani kurir
    public static function setAreaCoverage(Kurir $kurir, array $areas): void
    {
        self::setMetadata($kurir, self::META_AREA_COVERAGE, $areas);
    }

    // * Ambil informasi rekening bank kurir
    public static function getBankInfo(Kurir $kurir): ?array
    {
        return self::getMetadata($kurir, self::META_BANK_INFO);
    }

    // * Set informasi rekening bank kurir
    public static function setBankInfo(Kurir $kurir, array $bankData): void
    {
        self::setMetadata($kurir, self::META_BANK_INFO, $bankData);
    }

    // * Ambil kontak darurat kurir
    public static function getEmergencyContact(Kurir $kurir): ?array
    {
        return self::getMetadata($kurir, self::META_EMERGENCY_CONTACT);
    }

    // * Set kontak darurat kurir
    public static function setEmergencyContact(Kurir $kurir, array $contactData): void
    {
        self::setMetadata($kurir, self::META_EMERGENCY_CONTACT, $contactData);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_AREA_COVERAGE => 'nullable|array',
            self::META_BANK_INFO => 'nullable|array',
            self::META_EMERGENCY_CONTACT => 'nullable|array',
        ];
    }
}

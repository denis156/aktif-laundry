<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\User;

// ! Helper untuk mengelola metadata User
//
// ? Metadata yang didukung:
// * - alamat: Alamat rumah user
// * - shift: Shift kerja (pagi/siang/malam)
// * - gaji: Gaji pokok

class UserHelper
{
    public const META_ALAMAT = 'alamat';
    public const META_SHIFT = 'shift';
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

    // * Ambil alamat user
    public static function getAlamat(User $user): ?string
    {
        return self::getMetadata($user, self::META_ALAMAT);
    }

    // * Set alamat user
    public static function setAlamat(User $user, string $alamat): void
    {
        self::setMetadata($user, self::META_ALAMAT, $alamat);
    }

    // * Ambil shift kerja user
    public static function getShift(User $user): ?string
    {
        return self::getMetadata($user, self::META_SHIFT);
    }

    // * Set shift kerja user
    public static function setShift(User $user, string $shift): void
    {
        self::setMetadata($user, self::META_SHIFT, $shift);
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

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_ALAMAT => 'nullable|string',
            self::META_SHIFT => 'nullable|string|max:50',
            self::META_GAJI => 'nullable|integer|min:0',
        ];
    }
}

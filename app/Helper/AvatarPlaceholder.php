<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Helper untuk generate avatar placeholder dari nama menggunakan UI Avatars API
 *
 * Contoh URL:
 * - "Denis Ardika" -> https://ui-avatars.com/api/?name=Denis+Ardika&size=256
 * - Dengan custom color -> https://ui-avatars.com/api/?name=Denis&background=0D8ABC&color=fff
 */
class AvatarPlaceholder
{
    // Default settings untuk UI Avatars API
    private const API_URL = 'https://ui-avatars.com/api/';

    private const DEFAULT_SIZE = 256;

    private const DEFAULT_FONT_SIZE = 0.5; // 50% dari size

    private const DEFAULT_ROUNDED = true;

    private const DEFAULT_BOLD = true;

    /**
     * Generate URL avatar placeholder menggunakan UI Avatars API
     *
     * @param  string|null  $name  Nama untuk avatar
     * @param  int  $size  Ukuran avatar dalam pixel (default: 256)
     * @param  string|null  $background  Hex color untuk background (tanpa #)
     * @param  string|null  $color  Hex color untuk text (tanpa #)
     */
    public static function generate(?string $name, int $size = self::DEFAULT_SIZE, ?string $background = null, ?string $color = null): string
    {
        $name = trim($name ?? '');
        if (empty($name)) {
            $name = '??';
        }

        $params = [
            'name' => $name,
            'size' => $size,
            'font-size' => self::DEFAULT_FONT_SIZE,
            'rounded' => self::DEFAULT_ROUNDED ? 'true' : 'false',
            'bold' => self::DEFAULT_BOLD ? 'true' : 'false',
        ];

        // Tambahkan custom colors jika ada
        if ($background) {
            $params['background'] = ltrim($background, '#');
        }

        if ($color) {
            $params['color'] = ltrim($color, '#');
        }

        return self::API_URL.'?'.http_build_query($params);
    }

    /**
     * Generate URL avatar atau placeholder menggunakan UI Avatars API
     *
     * @param  string|null  $avatarUrl  Path ke avatar (tanpa asset/storage prefix)
     * @param  string|null  $name  Nama untuk placeholder
     * @param  int  $size  Ukuran avatar dalam pixel
     * @return string URL final (uploaded avatar atau UI Avatars API)
     */
    public static function getAvatarOrPlaceholder(?string $avatarUrl, ?string $name, int $size = self::DEFAULT_SIZE): string
    {
        // Jika ada avatar yang diupload, gunakan itu
        if ($avatarUrl) {
            return asset('storage/'.$avatarUrl);
        }

        // Jika tidak, gunakan UI Avatars API
        return self::generate($name, $size);
    }

    /**
     * Generate random background color (hex) untuk avatar, konsisten untuk nama yang sama
     * Bisa digunakan untuk custom background color di UI Avatars API
     *
     * @param  string  $name  Nama untuk generate warna konsisten
     * @return string Hex color tanpa # (misal: '0D8ABC')
     */
    public static function getColorFromName(string $name): string
    {
        $colors = [
            '0D8ABC', // Blue
            '6366F1', // Indigo
            '8B5CF6', // Purple
            'EC4899', // Pink
            'EF4444', // Red
            'F59E0B', // Amber
            '10B981', // Green
            '06B6D4', // Cyan
        ];

        // Hash nama untuk konsistensi warna
        $hash = crc32($name);
        $index = abs($hash) % count($colors);

        return $colors[$index];
    }

    /**
     * Generate avatar dengan warna konsisten berdasarkan nama
     *
     * @param  string|null  $name  Nama untuk avatar
     * @param  int  $size  Ukuran avatar dalam pixel
     * @return string URL UI Avatars API dengan warna konsisten
     */
    public static function generateWithConsistentColor(?string $name, int $size = self::DEFAULT_SIZE): string
    {
        $background = self::getColorFromName($name ?? '??');

        return self::generate($name, $size, $background, 'ffffff');
    }
}

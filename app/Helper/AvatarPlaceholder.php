<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Helper untuk generate avatar placeholder dari nama
 *
 * Contoh:
 * - "Denis Djodian Ardika" -> "DA"
 * - "Serli" -> "SE"
 * - "Denis" -> "DS"
 */
class AvatarPlaceholder
{
    /**
     * Generate placeholder 2 huruf dari nama
     *
     * Rules:
     * - Jika nama punya 2+ kata: ambil huruf pertama dari kata pertama dan kata terakhir
     * - Jika nama 1 kata: ambil 2 huruf pertama
     */
    public static function generate(?string $name): string
    {
        if (empty($name)) {
            return '??';
        }

        // Trim dan hapus extra spaces
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Pecah nama menjadi kata-kata
        $words = explode(' ', $name);

        // Filter kata-kata kosong
        $words = array_filter($words, fn ($word) => ! empty($word));

        if (empty($words)) {
            return '??';
        }

        // Jika hanya 1 kata, ambil 2 huruf pertama
        if (count($words) === 1) {
            $firstWord = $words[0];

            return strtoupper(mb_substr($firstWord, 0, 2));
        }

        // Jika 2+ kata, ambil huruf pertama dari kata pertama dan kata terakhir
        $firstWord = reset($words);  // Kata pertama
        $lastWord = end($words);     // Kata terakhir

        $firstLetter = mb_substr($firstWord, 0, 1);
        $lastLetter = mb_substr($lastWord, 0, 1);

        return strtoupper($firstLetter.$lastLetter);
    }

    /**
     * Generate URL avatar atau placeholder
     *
     * @param  string|null  $avatarUrl  Path ke avatar (tanpa asset/storage prefix)
     * @param  string|null  $name  Nama untuk placeholder
     */
    public static function getAvatarOrPlaceholder(?string $avatarUrl, ?string $name): array
    {
        return [
            'image' => $avatarUrl ? asset('storage/'.$avatarUrl) : null,
            'placeholder' => self::generate($name),
        ];
    }

    /**
     * Generate random background color untuk avatar (opsional untuk UI)
     * Konsisten untuk nama yang sama
     */
    public static function getColorFromName(string $name): string
    {
        $colors = [
            'bg-primary',
            'bg-secondary',
            'bg-accent',
            'bg-info',
            'bg-success',
            'bg-warning',
            'bg-error',
        ];

        // Hash nama untuk konsistensi warna
        $hash = crc32($name);
        $index = abs($hash) % count($colors);

        return $colors[$index];
    }
}

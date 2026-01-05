<?php

declare(strict_types=1);

namespace App\Helper;

class NumberFormat
{
    /**
     * Format number with Indonesian abbreviations (Rb, Jt, M, T)
     */
    public static function formatRupiah(float|int $number, int $decimals = 1): string
    {
        $absolute = abs($number);

        if ($absolute >= 1_000_000_000_000) {
            // Triliun
            $formatted = number_format($number / 1_000_000_000_000, $decimals, ',', '.');

            return $formatted.' T';
        } elseif ($absolute >= 1_000_000_000) {
            // Miliar
            $formatted = number_format($number / 1_000_000_000, $decimals, ',', '.');

            return $formatted.' M';
        } elseif ($absolute >= 1_000_000) {
            // Juta
            $formatted = number_format($number / 1_000_000, $decimals, ',', '.');

            return $formatted.' Jt';
        } elseif ($absolute >= 1_000) {
            // Ribu
            $formatted = number_format($number / 1_000, $decimals, ',', '.');

            return $formatted.' Rb';
        }

        // Less than 1000
        return number_format($number, 0, ',', '.');
    }
}

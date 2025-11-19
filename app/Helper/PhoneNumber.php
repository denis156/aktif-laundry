<?php

declare(strict_types=1);

namespace App\Helper;

// ! Helper untuk normalisasi dan validasi nomor telepon Indonesia
//
// ? Features:
// * - Normalisasi format nomor telepon ke format standar (mulai dari 8)
// * - Support berbagai format input: +62, 62, 08, 0, 8
// * - Validasi format nomor telepon Indonesia
// * - Format nomor untuk display (dengan +62 atau 0)
// * - Clean dan sanitize input nomor telepon
// * - Deteksi operator berdasarkan prefix
// * - Generate WhatsApp & Tel URL

class PhoneNumber
{
    // * Normalisasi nomor telepon ke format standar (mulai dari 8)
    public static function normalize(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove semua karakter non-digit (spasi, dash, kurung, dll)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika kosong setelah di-clean, return null
        if (empty($phone)) {
            return null;
        }

        // Remove prefix +62 atau 62
        if (str_starts_with($phone, '62')) {
            $phone = substr($phone, 2);
        }

        // Remove prefix 0
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        // Hasil akhir harus dimulai dari 8
        if (! str_starts_with($phone, '8')) {
            // Jika tidak dimulai dari 8, kemungkinan format salah
            return null;
        }

        return $phone;
    }

    // * Format nomor telepon untuk display dengan prefix +62
    public static function formatInternational(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        return '+62'.$normalized;
    }

    // * Format nomor telepon untuk display dengan prefix 0
    public static function formatLocal(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        return '0'.$normalized;
    }

    // * Format nomor telepon untuk display dengan pemisah (lebih readable)
    public static function formatReadable(?string $phone, bool $international = false): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        // Format: 08XX-XXXX-XXX atau 08XX-XXXX-XXXX
        $length = strlen($normalized);

        if ($length < 9 || $length > 13) {
            // Format tidak valid, return tanpa format
            return $international ? '+62'.$normalized : '0'.$normalized;
        }

        // Ambil 3 digit pertama (operator code)
        $part1 = substr($normalized, 0, 3);
        // Sisanya dibagi 2 bagian
        $remaining = substr($normalized, 3);
        $halfLength = (int) ceil(strlen($remaining) / 2);
        $part2 = substr($remaining, 0, $halfLength);
        $part3 = substr($remaining, $halfLength);

        $formatted = $part1.'-'.$part2.'-'.$part3;

        return $international ? '+62 '.$formatted : '0'.$formatted;
    }

    // * Validasi nomor telepon Indonesia (basic validation)
    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return false;
        }

        // Nomor telepon Indonesia: 9-13 digit setelah prefix
        $length = strlen($normalized);

        // Harus dimulai dari 8 dan panjang 9-13 digit (tidak cek operator)
        return str_starts_with($normalized, '8') && $length >= 9 && $length <= 13;
    }

    // * Dapatkan nama operator dari nomor telepon
    public static function getOperator(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        $prefix = substr($normalized, 0, 3);

        return match (true) {
            // Telkomsel: 0811-0813, 0821-0823, 0851-0853
            in_array($prefix, ['811', '812', '813', '821', '822', '823', '851', '852', '853']) => 'Telkomsel',

            // Indosat Ooredoo: 0814-0816, 0855-0858
            in_array($prefix, ['814', '815', '816', '855', '856', '857', '858']) => 'Indosat',

            // XL Axiata: 0817-0819, 0859, 0877-0879
            in_array($prefix, ['817', '818', '819', '859', '877', '878', '879']) => 'XL',

            // Axis: 0831-0833, 0838
            in_array($prefix, ['831', '832', '833', '838']) => 'Axis',

            // Three (Tri): 0895-0899
            in_array($prefix, ['895', '896', '897', '898', '899']) => 'Three',

            // Smartfren: 0881-0889
            in_array($prefix, ['881', '882', '883', '884', '885', '886', '887', '888', '889']) => 'Smartfren',

            default => 'Unknown', // Support nomor bisnis dan operator baru
        };
    }

    // * Generate URL WhatsApp dengan nomor telepon
    public static function getWhatsAppUrl(?string $phone, ?string $message = null): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        $url = 'https://wa.me/62'.$normalized;

        if (! empty($message)) {
            $url .= '?text='.urlencode($message);
        }

        return $url;
    }

    // * Generate URL untuk panggilan telepon (tel:)
    public static function getTelUrl(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        return 'tel:+62'.$normalized;
    }

    // * Mask nomor telepon untuk privacy (untuk display)
    public static function mask(?string $phone, bool $international = false): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized)) {
            return null;
        }

        $length = strlen($normalized);

        if ($length < 9) {
            return $international ? '+62'.$normalized : '0'.$normalized;
        }

        // Show first 3 and last 3 digits
        $prefix = substr($normalized, 0, 3);
        $suffix = substr($normalized, -3);
        $masked = $prefix.'****'.$suffix;

        return $international ? '+62 '.$masked : '0'.$masked;
    }

    // * Sanitize input nomor telepon (untuk form input)
    // * Remove karakter yang tidak valid, hanya biarkan digit, +, dan spasi
    public static function sanitize(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Hanya izinkan digit, +, spasi, dash, dan kurung
        return preg_replace('/[^0-9+\s\-()]/', '', $phone);
    }

    // * Compare dua nomor telepon (setelah dinormalisasi)
    public static function isSame(?string $phone1, ?string $phone2): bool
    {
        $normalized1 = self::normalize($phone1);
        $normalized2 = self::normalize($phone2);

        if (empty($normalized1) || empty($normalized2)) {
            return false;
        }

        return $normalized1 === $normalized2;
    }
}

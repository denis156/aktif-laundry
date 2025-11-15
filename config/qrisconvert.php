<?php

declare (strict_types=1);

/**
 * QRIS Configuration File
 *
 * File ini berisi konfigurasi QRIS Static untuk convert ke dinamis
 * QRIS Static code diambil dari environment variable QRIS_STATIC
 *
 * Penggunaan:
 * - QRIS Static code disimpan di .env sebagai QRIS_STATIC
 * - Digunakan oleh QrisHelper untuk generate QR dinamis
 * - Support biaya layanan (opsional)
 */

return [
    /*
    |--------------------------------------------------------------------------
    | QRIS Static Configuration
    |--------------------------------------------------------------------------
    |
    | QRIS Static code untuk convert ke dinamis dengan nominal
    | Diambil dari environment variable QRIS_STATIC
    |
    */

    'static_code' => env('QRIS_STATIC'),

    /*
    |--------------------------------------------------------------------------
    | QRIS Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk generate QR Code
    | - size: ukuran QR Code dalam pixel
    | - error_correction: level error correction (L, M, Q, H)
    | - margin: margin around QR Code
    |
    */

    'qr_code' => [
        'size' => 300,
        'error_correction' => 'H',
        'margin' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fee Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi biaya layanan (opsional)
    | - support: 'rupiah' atau 'persen'
    | - default: null (tanpa biaya)
    |
    */

    'fee' => [
        'type' => null, // 'rupiah' or 'persen' or null
        'amount' => null, // numeric value
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi storage untuk QR Code images
    | - disk: storage disk (default: public)
    * - path: path dalam storage (default: qrcodes)
    |
    */

    'storage' => [
        'disk' => 'public',
        'path' => 'qrcodes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi otomatis hapus QR Code lama
    | - retention_hours: berapa lama QR Code disimpan (dari env QRIS_RETENTION_HOURS)
    | - auto_cleanup: enable/disable otomatis cleanup (true jika retention > 0)
    |
    */

    'cleanup' => [
        'retention_hours' => (int) env('QRIS_RETENTION_HOURS', 72),
        'auto_cleanup' => (int) env('QRIS_RETENTION_HOURS', 72) > 0,
    ],
];

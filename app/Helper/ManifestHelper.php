<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Manifest Helper
 *
 * Helper untuk generate PWA manifest.json secara dynamic
 * Support untuk kurir dan pelanggan dengan konfigurasi berbeda
 */
class ManifestHelper
{
    /**
     * Generate manifest untuk aplikasi kurir
     *
     * @return array<string, mixed>
     */
    public static function kurirManifest(): array
    {
        return [
            'name' => 'Kurir '.config('app.name'),
            'short_name' => 'Kurir Aktif',
            'description' => 'Aplikasi kurir untuk Aktif Laundry - kelola pengiriman laundry dengan mudah',
            'start_url' => '/kurir/',
            'scope' => '/kurir/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#fd1a1a',
            'theme_color' => '#cf4040',
            'categories' => ['business', 'productivity'],
            'icons' => self::getIcons(),
        ];
    }

    /**
     * Generate manifest untuk aplikasi pelanggan
     *
     * @return array<string, mixed>
     */
    public static function pelangganManifest(): array
    {
        return [
            'name' => config('app.name'),
            'short_name' => 'Aktif Laundry',
            'description' => 'Aplikasi pelanggan Aktif Laundry - pesan dan lacak laundry Anda dengan mudah',
            'start_url' => '/pelanggan/',
            'scope' => '/pelanggan/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#fd1a1a',
            'theme_color' => '#cf4040',
            'categories' => ['lifestyle', 'utilities'],
            'icons' => self::getIcons(),
        ];
    }

    /**
     * Get array icons untuk PWA manifest
     * Icons yang sama digunakan untuk kurir dan pelanggan
     *
     * Chrome Android memerlukan minimal 1 icon dengan:
     * - Size: 192x192 atau lebih besar
     * - Purpose: 'any' atau 'maskable'
     * - Format: PNG (GIF tidak akan animate di native splash screen)
     *
     * Android akan otomatis generate splash screen dari:
     * - Icon PNG (512x512 atau 192x192)
     * - background_color dari manifest
     * - name dari manifest
     *
     * icon512_rounded.png - Icon dengan rounded corners untuk 'any' purpose
     * icon512_maskable.png - Icon dengan safe zone untuk 'maskable' (Android adaptive icons)
     *
     * @return array<int, array<string, string>>
     */
    private static function getIcons(): array
    {
        return [
            // Icon 512x512 rounded untuk splash screen & app icon - REQUIRED untuk Chrome Android
            [
                'src' => '/icon512_rounded.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            // Icon 512x512 maskable untuk Android adaptive icons
            [
                'src' => '/icon512_maskable.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            // Icon 192x192 fallback untuk device lama
            [
                'src' => '/icon512_rounded.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ];
    }
}

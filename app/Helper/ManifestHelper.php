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
            'short_name' => 'SiAktif',
            'description' => 'Aplikasi kurir untuk Aktif Laundry - kelola pengiriman laundry dengan mudah',
            'start_url' => '/kurir/',
            'scope' => '/kurir/',
            'display' => 'fullscreen',
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
            'display' => 'fullscreen',
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
     * @return array<int, array<string, string>>
     */
    private static function getIcons(): array
    {
        return [
            [
                'src' => '/icons/icon-48x48.png',
                'sizes' => '48x48',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-72x72.png',
                'sizes' => '72x72',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-96x96.png',
                'sizes' => '96x96',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-128x128.png',
                'sizes' => '128x128',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-144x144.png',
                'sizes' => '144x144',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-152x152.png',
                'sizes' => '152x152',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-256x256.png',
                'sizes' => '256x256',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-384x384.png',
                'sizes' => '384x384',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
        ];
    }
}

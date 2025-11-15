<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class QrisHelper
{
    /**
     * Convert QRIS Static to Dynamic dengan nominal
     */
    public static function generateDynamicQris(float $amount, ?string $feeType = null, ?float $feeAmount = null): string
    {
        try {
            // Ambil QRIS static dari config
            $qrisStatic = config('qrisconvert.static_code');

            if (empty($qrisStatic)) {
                throw new Exception('QRIS_STATIC not configured in environment');
            }

            // Convert ke format yang tepat (tanpa newline)
            $qrisStatic = trim(str_replace(["\n", "\r"], '', $qrisStatic));

            // Convert ke dinamis
            $dynamicQris = self::convertStaticToDynamic($qrisStatic, (string) $amount, $feeType, $feeAmount);

            return $dynamicQris;
        } catch (Exception $e) {
            Log::error('QRIS Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR Code image dan simpan ke storage
     */
    public static function generateQrCodeImage(string $qrisData, string $filename): string
    {
        try {
            // Get QR code config
            $qrConfig = config('qrisconvert.qr_code');
            $storageConfig = config('qrisconvert.storage');

            // Generate QR Code menggunakan Simple QrCode
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size($qrConfig['size'])
                ->errorCorrection($qrConfig['error_correction'])
                ->margin($qrConfig['margin'])
                ->generate($qrisData);

            // Save ke storage
            $path = $storageConfig['path'] . "/{$filename}";
            Storage::disk('public')->put($path, $qrCode);

            return $path;
        } catch (Exception $e) {
            Log::error('QR Code Image Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR Code untuk payment dengan nominal dari transaction (on-demand)
     */
    public static function generatePaymentQrCode(float $amount, int $transactionId): array
    {
        try {
            // Generate on-demand QR Code (SVG, tidak disimpan)
            $qrSvg = self::generateOnDemandQrCode($amount);

            return [
                'qris_data' => self::generateDynamicQris($amount),
                'qr_svg' => $qrSvg, // SVG string for display
                'amount' => $amount,
                'transaction_id' => $transactionId,
            ];
        } catch (Exception $e) {
            Log::error('Payment QR Code Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert static QRIS to dynamic dengan nominal
     * Logic dari config/qrisconvert.php
     */
    private static function convertStaticToDynamic(string $qris, string $amount, ?string $feeType = null, ?float $feeAmount = null): string
    {
        // Remove last 4 characters (CRC)
        $qris = substr($qris, 0, -4);

        // Change from static to dynamic
        $step1 = str_replace("010211", "010212", $qris);

        // Split at merchant info
        $step2 = explode("5802ID", $step1);

        // Build amount string
        $amountStr = "54" . sprintf("%02d", strlen($amount)) . $amount;

        // Handle fee jika ada
        $feeStr = '';
        if ($feeType && $feeAmount) {
            if ($feeType === 'rupiah') {
                $feeStr = "55020256" . sprintf("%02d", strlen((string)$feeAmount)) . $feeAmount;
            } elseif ($feeType === 'persen') {
                $feeStr = "55020357" . sprintf("%02d", strlen((string)$feeAmount)) . $feeAmount;
            }
        }

        // Combine all parts
        $uang = $amountStr . $feeStr . "5802ID";

        // Final QRIS string
        $fix = trim($step2[0]) . $uang . trim($step2[1]);

        // Add CRC16
        $fix .= self::calculateCRC16($fix);

        return $fix;
    }

    /**
     * Calculate CRC16 checksum
     */
    private static function calculateCRC16(string $str): string
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);

        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord(substr($str, $c, 1)) << 8;

            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        $hex = $crc & 0xFFFF;
        $hex = strtoupper(dechex($hex));

        return str_pad($hex, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Validate QRIS static format
     */
    public static function validateQrisStatic(string $qris): bool
    {
        // Basic validation - check if it starts with correct prefix
        return str_starts_with($qris, '000201010211');
    }

    /**
     * Generate QR Code on-demand (tidak disimpan, hanya untuk display)
     */
    public static function generateOnDemandQrCode(float $amount): string
    {
        try {
            // Generate dynamic QRIS
            $dynamicQris = self::generateDynamicQris($amount);

            // Generate QR Code string (base64 encoded)
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->margin(0)
                ->generate($dynamicQris);

            return (string) $qrCode;
        } catch (Exception $e) {
            Log::error('QR Code On-Demand Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR Code dengan storage (untuk download)
     */
    public static function generateStorableQrCode(float $amount, int $transactionId): array
    {
        try {
            // Auto cleanup old QR codes sebelum generate baru
            self::autoCleanupOldQrCodes();

            // Generate dynamic QRIS
            $dynamicQris = self::generateDynamicQris($amount);

            // Generate filename unik dengan timestamp yang lebih pendek
            $timestamp = time();
            $shortTimestamp = substr((string)$timestamp, -6); // 6 digit terakhir
            $filename = 'qr-' . $transactionId . '-' . $shortTimestamp . '.svg';

            // Generate QR Code SVG (lebih kecil dari PNG)
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(200) // lebih kecil untuk hemat storage
                ->errorCorrection('M') // error correction medium (lebih kecil)
                ->margin(1) // minimal margin
                ->generate($dynamicQris);

            // Convert to string for storage
            $qrCodeString = (string) $qrCode;

            // Save ke storage
            $storageConfig = config('qrisconvert.storage');
            $disk = $storageConfig['disk'];
            $path = $storageConfig['path'] . "/{$filename}";
            Storage::disk($disk)->put($path, $qrCodeString);

            return [
                'qris_data' => $dynamicQris,
                'image_path' => $path,
                'image_url' => asset('storage/' . $path),
                'amount' => $amount,
                'file_size' => strlen($qrCodeString),
            ];
        } catch (Exception $e) {
            Log::error('QR Code Storable Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up old QR Code files (lebih dari X jam)
     */
    public static function cleanupOldQrCodes(int $hoursOld = 24): array
    {
        try {
            $storageConfig = config('qrisconvert.storage');
            $disk = $storageConfig['disk'];
            $path = $storageConfig['path'];

            $files = Storage::disk($disk)->files($path);
            $deletedFiles = [];
            $totalSize = 0;

            foreach ($files as $file) {
                $fileName = basename($file);
                if (!str_starts_with($fileName, 'qr-')) {
                    continue; // skip non-QR files
                }

                $filePath = $file;

                // Get last modified time
                $lastModified = Storage::disk($disk)->lastModified($filePath);
                $ageHours = (time() - $lastModified) / 3600;

                if ($ageHours > $hoursOld) {
                    $fileSize = Storage::disk($disk)->size($filePath);

                    if (Storage::disk($disk)->delete($filePath)) {
                        $deletedFiles[] = [
                            'file' => $fileName,
                            'age_hours' => round($ageHours, 2),
                            'size_bytes' => $fileSize,
                        ];
                        $totalSize += $fileSize;
                    }
                }
            }

            return [
                'deleted_count' => count($deletedFiles),
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'files' => $deletedFiles,
            ];
        } catch (Exception $e) {
            Log::error('QR Code Cleanup Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get storage usage statistics
     */
    public static function getStorageStats(): array
    {
        try {
            $storageConfig = config('qrisconvert.storage');
            $disk = $storageConfig['disk'];
            $path = $storageConfig['path'];

            $files = Storage::disk($disk)->files($path);
            $totalFiles = 0;
            $totalSize = 0;
            $oldestFile = null;
            $newestFile = null;

            foreach ($files as $file) {
                $fileName = basename($file);
                if (!str_starts_with($fileName, 'qr-')) {
                    continue;
                }

                $filePath = $file;
                $fileSize = Storage::disk($disk)->size($filePath);
                $lastModified = Storage::disk($disk)->lastModified($filePath);

                $totalFiles++;
                $totalSize += $fileSize;

                if (!$oldestFile || $lastModified < $oldestFile['time']) {
                    $oldestFile = [
                        'file' => $fileName,
                        'time' => $lastModified,
                        'age_hours' => round((time() - $lastModified) / 3600, 2),
                    ];
                }

                if (!$newestFile || $lastModified > $newestFile['time']) {
                    $newestFile = [
                        'file' => $fileName,
                        'time' => $lastModified,
                        'age_hours' => round((time() - $lastModified) / 3600, 2),
                    ];
                }
            }

            return [
                'total_files' => $totalFiles,
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'oldest_file' => $oldestFile,
                'newest_file' => $newestFile,
            ];
        } catch (Exception $e) {
            Log::error('QR Code Storage Stats Error: ' . $e->getMessage());
            return [
                'total_files' => 0,
                'total_size_bytes' => 0,
                'total_size_mb' => 0,
                'oldest_file' => null,
                'newest_file' => null,
            ];
        }
    }

    /**
     * Auto cleanup old QR codes berdasarkan konfigurasi
     * Dipanggil otomatis saat generate QR code baru
     */
    private static function autoCleanupOldQrCodes(): void
    {
        try {
            $cleanupConfig = config('qrisconvert.cleanup');

            // Skip jika auto_cleanup disabled
            if (!$cleanupConfig['auto_cleanup']) {
                return;
            }

            $retentionHours = $cleanupConfig['retention_hours'];

            // Skip jika retention hours = 0 atau kurang dari 1
            if ($retentionHours <= 0) {
                return;
            }

            // Lakukan cleanup
            $result = self::cleanupOldQrCodes($retentionHours);

            // Log hasil cleanup (hanya jika ada file yang dihapus)
            if ($result['deleted_count'] > 0) {
                Log::info('QR Code Auto Cleanup: Deleted ' . $result['deleted_count'] .
                         ' old QR files, freed up ' . $result['total_size_mb'] . ' MB');
            }
        } catch (Exception $e) {
            // Log error tapi jangan block proses generate QR
            Log::warning('QR Code Auto Cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Format amount untuk display
     */
    public static function formatAmount(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

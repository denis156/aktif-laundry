<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    // Cache TTL dalam detik
    public const CACHE_TTL_DEVICES = 300; // 5 menit

    public const CACHE_TTL_DEVICE_PROFILE = 120; // 2 menit

    /**
     * Send WhatsApp message via Fonnte API
     *
     * @param  string  $target  WhatsApp number (e.g., 628123456789)
     * @param  string  $message  Message text to send
     * @return Response HTTP response from Fonnte API
     *
     * @throws Exception
     */
    public function sendMessage(string $target, string $message): Response
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.device_token'),
            ])
                ->timeout(30)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

            // Log successful sends
            if ($response->successful()) {
                Log::info('WhatsApp message sent via Fonnte', [
                    'target' => $target,
                    'message_length' => strlen($message),
                ]);
            } else {
                Log::warning('Failed to send WhatsApp message via Fonnte', [
                    'target' => $target,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Fonnte API error: '.$e->getMessage(), [
                'target' => $target,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Send WhatsApp message with media
     *
     * @param  string  $target  WhatsApp number
     * @param  string  $message  Message text
     * @param  string  $url  URL of media file (image, document, etc.)
     * @param  string  $filename  Optional filename for document
     * @return Response HTTP response from Fonnte API
     *
     * @throws Exception
     */
    public function sendMessageWithMedia(string $target, string $message, string $url, ?string $filename = null): Response
    {
        try {
            $data = [
                'target' => $target,
                'message' => $message,
                'url' => $url,
            ];

            if ($filename !== null) {
                $data['filename'] = $filename;
            }

            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.device_token'),
            ])
                ->timeout(30)
                ->post('https://api.fonnte.com/send', $data);

            if ($response->successful()) {
                Log::info('WhatsApp message with media sent via Fonnte', [
                    'target' => $target,
                    'media_url' => $url,
                ]);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Fonnte API error (with media): '.$e->getMessage(), [
                'target' => $target,
                'media_url' => $url,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Get all Fonnte devices with caching
     *
     * @return array Response with status and data/error
     */
    public function getAllDevices(): array
    {
        // Check cache first
        $cacheKey = 'fonnte_devices_list';
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.fonnte_token'),
            ])
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/get-devices', []);

            // Parse response
            $responseData = $response->json();

            // Check if response indicates error
            if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                Log::error('Fonnte API Error: Failed to get devices', [
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ]);

                return [
                    'status' => false,
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ];
            }

            $result = [
                'status' => true,
                'data' => $responseData,
            ];

            // Cache successful response
            Cache::put($cacheKey, $result, self::CACHE_TTL_DEVICES);

            return $result;
        } catch (Exception $e) {
            Log::error('Fonnte API Exception: Failed to get devices', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Clear cache for device list
     */
    public function clearDevicesCache(): void
    {
        Cache::forget('fonnte_devices_list');
    }

    /**
     * Add new Fonnte device
     *
     * @param  string  $name  Device name
     * @param  string  $phoneNumber  Phone number with 62 prefix
     * @return array Response with status and data/error
     */
    public function addDevice(string $name, string $phoneNumber): array
    {
        try {
            $params = [
                'name' => $name,
                'device' => $phoneNumber,
                'autoread' => false,
                'personal' => true,
                'group' => false,
            ];

            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.fonnte_token'),
                'Content-Type' => 'application/json',
            ])
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/add-device', $params);

            $responseData = $response->json();

            if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                Log::error('Fonnte API Error: Failed to add device', [
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ]);

                return [
                    'status' => false,
                    'error' => $responseData['reason'] ?? 'Invalid or empty body value',
                ];
            }

            // Clear cache after adding device
            $this->clearDevicesCache();

            return [
                'status' => true,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error('Fonnte API Exception: Failed to add device', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Get device profile with caching
     *
     * @param  string  $deviceToken  Device token
     * @return array Response with status and data/error
     */
    public function getDeviceProfile(string $deviceToken): array
    {
        $cacheKey = 'fonnte_device_profile_'.$deviceToken;

        return Cache::remember($cacheKey, self::CACHE_TTL_DEVICE_PROFILE, function () use ($deviceToken) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $deviceToken,
                ])
                    ->timeout(10)
                    ->connectTimeout(5)
                    ->post('https://api.fonnte.com/device', []);

                $responseData = $response->json();

                if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                    Log::error('Fonnte API Error: Failed to get device profile', [
                        'error' => $responseData['reason'] ?? 'Unknown error occurred',
                    ]);

                    return [
                        'status' => false,
                        'error' => $responseData['reason'] ?? 'Unknown error occurred',
                    ];
                }

                return [
                    'status' => true,
                    'data' => $responseData,
                ];
            } catch (Exception $e) {
                Log::error('Fonnte API Exception: Failed to get device profile', [
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);

                return [
                    'status' => false,
                    'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
                ];
            }
        });
    }

    /**
     * Clear cache for specific device profile
     */
    public function clearDeviceProfileCache(string $deviceToken): void
    {
        Cache::forget('fonnte_device_profile_'.$deviceToken);
    }

    /**
     * Update device name
     *
     * @param  string  $deviceToken  Device token
     * @param  string  $name  New device name
     * @return array Response with status and data/error
     */
    public function updateDevice(string $deviceToken, string $name): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $deviceToken,
                'Content-Type' => 'application/json',
            ])
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/update-device', [
                    'name' => $name,
                ]);

            $responseData = $response->json();

            if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                Log::error('Fonnte API Error: Failed to update device', [
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ]);

                return [
                    'status' => false,
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ];
            }

            // Clear cache after update
            $this->clearDeviceProfileCache($deviceToken);
            $this->clearDevicesCache();

            return [
                'status' => true,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error('Fonnte API Exception: Failed to update device', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Request QR code activation for device
     *
     * @param  string  $phoneNumber  Phone number with 62 prefix
     * @param  string  $deviceToken  Device token
     * @return array Response with status and data/error
     */
    public function requestQRActivation(string $phoneNumber, string $deviceToken): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $deviceToken,
                'Content-Type' => 'application/json',
            ])
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/qr', [
                    'type' => 'qr',
                    'whatsapp' => $phoneNumber,
                ]);

            $responseData = $response->json();

            if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                Log::error('Fonnte API Error: Failed to request QR', [
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ]);

                return [
                    'status' => false,
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ];
            }

            // Format QR code from response
            if (isset($responseData['url'])) {
                $responseData['qrcode'] = 'data:image/png;base64,'.$responseData['url'];
            }

            return [
                'status' => true,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error('Fonnte API Exception: Failed to request QR', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
            ];
        }
    }

    /**
     * Disconnect device from WhatsApp
     *
     * @param  string  $deviceToken  Device token
     * @return array Response with status and data/error
     */
    public function disconnectDevice(string $deviceToken): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $deviceToken,
            ])
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.fonnte.com/disconnect', []);

            $responseData = $response->json();

            if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
                Log::error('Fonnte API Error: Failed to disconnect device', [
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ]);

                return [
                    'status' => false,
                    'error' => $responseData['reason'] ?? 'Unknown error occurred',
                ];
            }

            // Clear cache after disconnect
            $this->clearDeviceProfileCache($deviceToken);
            $this->clearDevicesCache();

            return [
                'status' => true,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            Log::error('Fonnte API Exception: Failed to disconnect device', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat terhubung ke Fonnte API. Silakan coba lagi nanti.',
            ];
        }
    }
}

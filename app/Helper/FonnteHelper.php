<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    protected $fonnte_token;

    // Cache TTL dalam detik (5 menit untuk device list, 2 menit untuk device profile)
    public const CACHE_TTL_DEVICES = 300;

    public const CACHE_TTL_DEVICE_PROFILE = 120;

    // Konstanta endpoint API Fonnte
    public const ENDPOINTS = [
        'send_message' => 'https://api.fonnte.com/send',
        'add_device' => 'https://api.fonnte.com/add-device',
        'qr_activation' => 'https://api.fonnte.com/qr',
        'get_devices' => 'https://api.fonnte.com/get-devices',
        'device_profile' => 'https://api.fonnte.com/device',
        'update_device' => 'https://api.fonnte.com/update-device',
        'delete_device' => 'https://api.fonnte.com/delete-device',
        'disconnect' => 'https://api.fonnte.com/disconnect',

    ];

    public function __construct()
    {
        $this->fonnte_token = env('FONNTE_TOKEN');
    }

    protected function makeRequest($endpoint, $params = [], $useAccountToken = true, $deviceToken = null)
    {
        $token = $useAccountToken
            ? $this->fonnte_token
            : ($deviceToken ?? null);

        if (! $token) {
            return ['status' => false, 'error' => 'API token or device token is required.'];
        }

        // Gunakan JSON format dengan Content-Type header yang benar
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $params);

        // Log respons untuk memudahkan debugging
        Log::info('Fonnte API Response', [
            'endpoint' => $endpoint,
            'params' => $params,
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        // Parse response
        $responseData = $response->json();

        // Check if response indicates error
        if ($response->failed() || (isset($responseData['status']) && $responseData['status'] === false)) {
            return [
                'status' => false,
                'error' => $responseData['reason'] ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $responseData,
        ];
    }

    public function sendWhatsAppMessage($phoneNumber, $message, $deviceToken)
    {
        return $this->makeRequest(self::ENDPOINTS['send_message'], [
            'target' => $phoneNumber,
            'message' => $message,
        ], '', $deviceToken);
    }

    public function getAllDevices()
    {
        // Cek cache terlebih dahulu
        $cacheKey = 'fonnte_devices_list';

        return Cache::remember($cacheKey, self::CACHE_TTL_DEVICES, function () {
            $response = $this->makeRequest(self::ENDPOINTS['get_devices'], [], true);

            // Check if API returns error in data
            if ($response['status'] && isset($response['data']['status']) && $response['data']['status'] === false) {
                return [
                    'status' => false,
                    'error' => $response['data']['reason'] ?? 'Unknown error from Fonnte API',
                ];
            }

            return $response;
        });
    }

    /**
     * Clear cache untuk device list
     */
    public function clearDevicesCache(): void
    {
        Cache::forget('fonnte_devices_list');
    }

    public function addDevice($name, $phoneNumber)
    {
        // Fonnte API menggunakan form data dengan boolean values
        $params = [
            'name' => $name,
            'device' => $phoneNumber,
            'autoread' => false,  // boolean, bukan string
            'personal' => true,   // boolean, bukan string
            'group' => false,     // boolean, bukan string
        ];

        // Log request untuk memastikan payload benar
        Log::info('Fonnte Add Device Request', ['params' => $params]);

        // Kirim request
        $response = $this->makeRequest(self::ENDPOINTS['add_device'], $params, true);

        // Cek dan log respons API
        if (! $response['status'] || empty($response['data']['status'])) {
            Log::error('Failed to add device', ['response' => $response]);

            return [
                'status' => false,
                'error' => $response['error'] ?? 'Invalid or empty body value',
            ];
        }

        // Clear cache setelah menambah device
        $this->clearDevicesCache();

        return [
            'status' => true,
            'data' => $response['data'],
        ];
    }

    public function requestQRActivation($phoneNumber, $deviceToken)
    {
        $response = $this->makeRequest(self::ENDPOINTS['qr_activation'], [
            'type' => 'qr',
            'whatsapp' => $phoneNumber,
        ], false, $deviceToken);

        // Format QR code dari response API
        if ($response['status'] && isset($response['data']['url'])) {
            // API Fonnte mengembalikan base64 image di field 'url'
            // Format sebagai data URI untuk ditampilkan di <img> tag
            $response['data']['qrcode'] = 'data:image/png;base64,'.$response['data']['url'];
        }

        return $response;
    }

    public function getDeviceProfile($deviceToken)
    {
        // Cek cache terlebih dahulu
        $cacheKey = 'fonnte_device_profile_'.$deviceToken;

        return Cache::remember($cacheKey, self::CACHE_TTL_DEVICE_PROFILE, function () use ($deviceToken) {
            return $this->makeRequest(self::ENDPOINTS['device_profile'], [], false, $deviceToken);
        });
    }

    /**
     * Clear cache untuk device profile tertentu
     */
    public function clearDeviceProfileCache(string $deviceToken): void
    {
        Cache::forget('fonnte_device_profile_'.$deviceToken);
    }

    public function disconnectDevice($deviceToken)
    {
        $response = $this->makeRequest(self::ENDPOINTS['disconnect'], [], false, $deviceToken);

        // Clear cache setelah disconnect
        if ($response['status']) {
            $this->clearDeviceProfileCache($deviceToken);
            $this->clearDevicesCache();
        }

        return $response;
    }

    public function updateDevice($deviceToken, $name)
    {
        Log::info('Update device', [
            'device_token' => $deviceToken,
            'name' => $name,
        ]);

        $response = $this->makeRequest(self::ENDPOINTS['update_device'], [
            'name' => $name,
        ], false, $deviceToken);

        // Clear cache setelah update berhasil
        if ($response['status']) {
            $this->clearDeviceProfileCache($deviceToken);
            $this->clearDevicesCache();
        }

        return $response;
    }

    /**
     * Request OTP untuk delete device
     * Kirim dengan 'otp' => '' (string kosong) untuk meminta kode OTP
     */
    public function requestOTPForDeleteDevice($deviceToken)
    {
        Log::info('Request OTP untuk delete device', ['device_token' => $deviceToken]);

        $response = $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => ''], false, $deviceToken);

        // Jika mendapat error "invalid confirmation", berikan pesan yang lebih informatif
        if (! $response['status'] && isset($response['error']) && str_contains($response['error'], 'invalid confirmation')) {
            Log::warning('Fonnte API delete-device mengembalikan invalid confirmation', [
                'device_token' => $deviceToken,
                'response' => $response,
            ]);

            return [
                'status' => false,
                'error' => 'Tidak dapat mengirim OTP. Kemungkinan: (1) Fitur delete via API tidak tersedia untuk akun ini, (2) Device perlu setting khusus di dashboard Fonnte, atau (3) API sedang bermasalah. Silakan coba hapus device melalui dashboard Fonnte atau hubungi support Fonnte.',
            ];
        }

        return $response;
    }

    /**
     * Submit OTP untuk confirm delete device
     * Kirim OTP code yang diterima via WhatsApp
     */
    public function submitOTPForDeleteDevice($otp, $deviceToken)
    {
        Log::info('Menghapus perangkat dengan OTP', ['otp' => $otp, 'device_token' => $deviceToken]);

        $response = $this->makeRequest(self::ENDPOINTS['delete_device'], ['otp' => (int) $otp], false, $deviceToken);

        if ($response['status']) {
            // Clear cache setelah delete berhasil
            $this->clearDeviceProfileCache($deviceToken);
            $this->clearDevicesCache();
        }

        return $response;
    }

    public function getDeviceStatus($phoneNumber)
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.account_token'), // Ensure you're using the correct token
        ])->get(self::ENDPOINTS['check_device_status'], [
            'whatsapp' => $phoneNumber,
        ]);

        if ($response->failed()) {
            return [
                'status' => false,
                'error' => $response->body() ?? 'Unknown error occurred',
            ];
        }

        return [
            'status' => true,
            'data' => $response->json(),
        ];
    }
}

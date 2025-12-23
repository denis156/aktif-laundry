<?php

declare(strict_types=1);

namespace App\Helper;

use App\Services\FirebaseService;
use Exception;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    /**
     * Send notification to a single user by role and ID
     *
     * @param  string  $role  Role: 'user', 'kurir', or 'pelanggan'
     * @param  int  $userId  User ID for the specific role
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToUser(string $role, int $userId, string $title, string $message, array $data = []): bool
    {
        try {
            $service = app(FirebaseService::class);
            $service->sendToUser($role, $userId, $title, $message, $data);

            return true;
        } catch (Exception $e) {
            Log::error('FirebaseHelper::sendToUser failed', [
                'role' => $role,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to multiple users by role
     *
     * @param  string  $role  Role: 'user', 'kurir', or 'pelanggan'
     * @param  array  $userIds  Array of user IDs
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToMultipleUsers(string $role, array $userIds, string $title, string $message, array $data = []): bool
    {
        try {
            $service = app(FirebaseService::class);
            $service->sendToMultipleUsers($role, $userIds, $title, $message, $data);

            return true;
        } catch (Exception $e) {
            Log::error('FirebaseHelper::sendToMultipleUsers failed', [
                'role' => $role,
                'user_count' => count($userIds),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to all users of a specific role
     *
     * @param  string  $role  Role: 'user', 'kurir', or 'pelanggan'
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToAllUsersOfRole(string $role, string $title, string $message, array $data = []): bool
    {
        try {
            $service = app(FirebaseService::class);
            $service->sendToAllUsersOfRole($role, $title, $message, $data);

            return true;
        } catch (Exception $e) {
            Log::error('FirebaseHelper::sendToAllUsersOfRole failed', [
                'role' => $role,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to specific device tokens
     *
     * @param  array  $tokens  Array of FCM device tokens
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToTokens(array $tokens, string $title, string $message, array $data = []): bool
    {
        try {
            $service = app(FirebaseService::class);
            $service->send($title, $message, $tokens, $data);

            return true;
        } catch (Exception $e) {
            Log::error('FirebaseHelper::sendToTokens failed', [
                'token_count' => count($tokens),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to Management/Admin user
     *
     * @param  int  $userId  User ID
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToManagement(int $userId, string $title, string $message, array $data = []): bool
    {
        return self::sendToUser('user', $userId, $title, $message, $data);
    }

    /**
     * Send notification to Kurir user
     *
     * @param  int  $kurirId  Kurir ID
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToKurir(int $kurirId, string $title, string $message, array $data = []): bool
    {
        return self::sendToUser('kurir', $kurirId, $title, $message, $data);
    }

    /**
     * Send notification to Pelanggan user
     *
     * @param  int  $pelangganId  Pelanggan ID
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToPelanggan(int $pelangganId, string $title, string $message, array $data = []): bool
    {
        return self::sendToUser('pelanggan', $pelangganId, $title, $message, $data);
    }

    /**
     * Send notification to all Management/Admin users
     *
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToAllManagement(string $title, string $message, array $data = []): bool
    {
        return self::sendToAllUsersOfRole('user', $title, $message, $data);
    }

    /**
     * Send notification to all Kurir users
     *
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToAllKurir(string $title, string $message, array $data = []): bool
    {
        return self::sendToAllUsersOfRole('kurir', $title, $message, $data);
    }

    /**
     * Send notification to all Pelanggan users
     *
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return bool Success status
     */
    public static function sendToAllPelanggan(string $title, string $message, array $data = []): bool
    {
        return self::sendToAllUsersOfRole('pelanggan', $title, $message, $data);
    }

    /**
     * Send notification broadcast to all users (all roles)
     *
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload (optional)
     * @return array Success status for each role
     */
    public static function sendBroadcast(string $title, string $message, array $data = []): array
    {
        $results = [
            'management' => self::sendToAllManagement($title, $message, $data),
            'kurir' => self::sendToAllKurir($title, $message, $data),
            'pelanggan' => self::sendToAllPelanggan($title, $message, $data),
        ];

        Log::info('FirebaseHelper::sendBroadcast completed', [
            'title' => $title,
            'results' => $results,
        ]);

        return $results;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Send Firebase Cloud Messaging notification
     *
     * @param  string  $heading  Notification title
     * @param  string  $message  Notification body/message
     * @param  array  $deviceTokens  Array of FCM device tokens
     * @param  array  $data  Additional data payload (optional)
     *
     * @throws Exception
     */
    public function send(string $heading, string $message, array $deviceTokens, array $data = []): Response|array
    {
        // Filter out empty/null tokens
        $deviceTokens = array_values(array_filter($deviceTokens));

        if (empty($deviceTokens)) {
            throw new Exception('No valid device tokens provided');
        }

        try {
            // Get OAuth 2.0 access token using Service Account credentials
            $accessToken = $this->getAccessToken();
            $projectId = config('services.firebase.web.project_id');

            // Build the FCM message payload
            $messagePayload = [
                'notification' => [
                    'title' => $heading,
                    'body' => $message,
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
                'webpush' => [
                    'notification' => [
                        'icon' => config('app.url').'/icons/icon-512x512.png',
                        'badge' => config('app.url').'/icons/icon-512x512.png',
                    ],
                    'fcm_options' => [
                        'link' => config('app.url'),
                    ],
                ],
            ];

            // Add custom data payload if provided
            if (! empty($data)) {
                $messagePayload['data'] = array_map('strval', $data);
            }

            // Single token or multiple tokens
            if (count($deviceTokens) === 1) {
                $messagePayload['token'] = $deviceTokens[0];

                return $this->sendSingleMessage($projectId, $messagePayload, $accessToken);
            } else {
                // For multiple tokens, send batch
                return $this->sendBatchMessages($projectId, $messagePayload, $deviceTokens, $accessToken);
            }
        } catch (Exception $e) {
            Log::error('Firebase notification error: '.$e->getMessage(), [
                'heading' => $heading,
                'tokens_count' => count($deviceTokens),
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Send notification to single device token
     */
    protected function sendSingleMessage(string $projectId, array $messagePayload, string $accessToken): Response
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        return Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 100)
            ->post($url, [
                'message' => $messagePayload,
            ]);
    }

    /**
     * Send notifications to multiple device tokens
     */
    protected function sendBatchMessages(string $projectId, array $messagePayload, array $tokens, string $accessToken): array
    {
        $responses = [];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($tokens as $token) {
            try {
                $messagePayload['token'] = $token;

                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->timeout(30)
                    ->retry(2, 100)
                    ->post($url, [
                        'message' => $messagePayload,
                    ]);

                $responses[] = [
                    'token' => $token,
                    'success' => $response->successful(),
                    'response' => $response->json(),
                    'status' => $response->status(),
                ];
            } catch (Exception $e) {
                Log::warning('Failed to send notification to token', [
                    'token' => substr($token, 0, 20).'...',
                    'error' => $e->getMessage(),
                ]);

                $responses[] = [
                    'token' => $token,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    /**
     * Get OAuth 2.0 access token from Service Account credentials
     */
    protected function getAccessToken(): string
    {
        $credentialsPath = config('services.firebase.credentials');

        // If path starts with 'storage/', it's relative to project root
        if (str_starts_with($credentialsPath, 'storage/')) {
            $credentialsPath = base_path($credentialsPath);
        }
        // If path doesn't start with '/', assume it's relative to storage directory
        elseif (! str_starts_with($credentialsPath, '/')) {
            $credentialsPath = storage_path($credentialsPath);
        }

        if (! file_exists($credentialsPath)) {
            throw new Exception("Firebase credentials file not found at: {$credentialsPath}");
        }

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

        $authToken = $credentials->fetchAuthToken();

        if (! isset($authToken['access_token'])) {
            throw new Exception('Failed to fetch Firebase access token');
        }

        return $authToken['access_token'];
    }

    /**
     * Send notification to specific user role
     *
     * @param  string  $role  Role name: 'user', 'kurir', or 'pelanggan'
     * @param  int  $userId  User ID for the specific role
     * @param  string  $heading  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload
     *
     * @throws Exception
     */
    public function sendToUser(string $role, int $userId, string $heading, string $message, array $data = []): Response|array
    {
        $fcmToken = match ($role) {
            'user', 'management', 'admin' => \App\Models\User::find($userId)?->fcm_token,
            'kurir' => \App\Models\Kurir::find($userId)?->fcm_token,
            'pelanggan' => \App\Models\Pelanggan::find($userId)?->fcm_token,
            default => throw new Exception("Invalid role: {$role}"),
        };

        if (! $fcmToken) {
            throw new Exception("No FCM token found for {$role} with ID {$userId}");
        }

        return $this->send($heading, $message, [$fcmToken], $data);
    }

    /**
     * Send notification to multiple users of specific role
     *
     * @param  string  $role  Role name: 'user', 'kurir', or 'pelanggan'
     * @param  array  $userIds  Array of user IDs
     * @param  string  $heading  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload
     *
     * @throws Exception
     */
    public function sendToMultipleUsers(string $role, array $userIds, string $heading, string $message, array $data = []): Response|array
    {
        $tokens = match ($role) {
            'user', 'management', 'admin' => \App\Models\User::whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            'kurir' => \App\Models\Kurir::whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            'pelanggan' => \App\Models\Pelanggan::whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            default => throw new Exception("Invalid role: {$role}"),
        };

        if (empty($tokens)) {
            throw new Exception("No FCM tokens found for the specified {$role} users");
        }

        return $this->send($heading, $message, $tokens, $data);
    }

    /**
     * Send notification to all users of specific role
     *
     * @param  string  $role  Role name: 'user', 'kurir', or 'pelanggan'
     * @param  string  $heading  Notification title
     * @param  string  $message  Notification body
     * @param  array  $data  Additional data payload
     *
     * @throws Exception
     */
    public function sendToAllUsersOfRole(string $role, string $heading, string $message, array $data = []): Response|array
    {
        $tokens = match ($role) {
            'user', 'management', 'admin' => \App\Models\User::whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            'kurir' => \App\Models\Kurir::whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            'pelanggan' => \App\Models\Pelanggan::whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray(),
            default => throw new Exception("Invalid role: {$role}"),
        };

        if (empty($tokens)) {
            throw new Exception("No FCM tokens found for role: {$role}");
        }

        return $this->send($heading, $message, $tokens, $data);
    }
}

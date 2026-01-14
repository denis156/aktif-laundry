<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChatbotSetting;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaiService
{
    /**
     * Get chatbot settings from database with caching
     */
    protected function getActiveChatbotSetting(): ?ChatbotSetting
    {
        return Cache::remember('active_chatbot_setting', 300, function () {
            return ChatbotSetting::getActive();
        });
    }

    /**
     * Get system prompt from active chatbot setting
     */
    protected function getSystemPrompt(): ?string
    {
        $setting = $this->getActiveChatbotSetting();

        return $setting?->system_prompt;
    }

    /**
     * Get settings for Z.AI API request
     *
     * @return array{max_tokens: int, temperature: float, timeout: int}
     */
    protected function getApiSettings(): array
    {
        $setting = $this->getActiveChatbotSetting();

        return [
            'max_tokens' => $setting?->max_tokens ?? (int) config('services.zai.max_tokens'),
            'temperature' => $setting?->temperature ?? (float) config('services.zai.temperature'),
            'timeout' => $setting?->timeout ?? (int) config('services.zai.timeout'),
        ];
    }

    /**
     * Send message to Z.AI and get response with context
     *
     * @param  string  $message  User message to send to AI
     * @param  string|null  $contextData  Additional context data to inject
     * @return string AI response text
     *
     * @throws Exception
     */
    public function chat(string $message, ?string $contextData = null): string
    {
        try {
            $settings = $this->getApiSettings();
            $systemPrompt = $this->getSystemPrompt();

            // Inject context data into system prompt if provided
            if ($contextData && $systemPrompt) {
                $systemPrompt .= "\n\n".$contextData;
            }

            $payload = [
                'model' => config('services.zai.model'),
                'max_tokens' => $settings['max_tokens'],
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                'temperature' => $settings['temperature'],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => config('services.zai.api_key'),
            ])
                ->timeout($settings['timeout'])
                ->connectTimeout(10)
                ->post(config('services.zai.api_url'), $payload);

            return $this->extractResponse($response);
        } catch (Exception $e) {
            Log::error('Z.AI API error: '.$e->getMessage(), [
                'message' => $message,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Extract AI response from Z.AI API response
     *
     * @param  Response  $response  HTTP response from Z.AI
     * @return string Extracted message text
     *
     * @throws Exception
     */
    protected function extractResponse(Response $response): string
    {
        // Check for HTTP errors
        if (! $response->successful()) {
            $httpCode = $response->status();
            $errorMessage = $response->json('error.message', 'Unknown error');

            throw new Exception("Z.AI API returned HTTP {$httpCode}: {$errorMessage}");
        }

        $data = $response->json();

        // Handle error response from Z.AI
        if (isset($data['error'])) {
            $errorMsg = $data['error']['message'] ?? 'Unknown error';

            throw new Exception("Z.AI error: {$errorMsg}");
        }

        // Extract response text (Anthropic format)
        $reply = $data['content'][0]['text']
            ?? $data['choices'][0]['message']['content'] // fallback to OpenAI format
            ?? null;

        if ($reply === null) {
            throw new Exception('Invalid response format from Z.AI');
        }

        return $reply;
    }

    /**
     * Clear cached chatbot settings
     */
    public static function clearCache(): void
    {
        Cache::forget('active_chatbot_setting');
    }
}

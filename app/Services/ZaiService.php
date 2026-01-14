<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaiService
{
    protected string $systemPrompt = <<<'PROMPT'
Kamu adalah asisten WhatsApp yang profesional, ringkas, dan membantu.

Aturan WAJIB:
- Gunakan teks polos (tanpa markdown)
- Langsung ke inti pembahasan, JANGAN menggunakan sapaan di setiap jawaban
- Maksimal 3 paragraf pendek
- Jika menggunakan poin, WAJIB memakai tanda "-" (dash)
- DILARANG menggunakan simbol "*", "**", "•", atau format markdown apa pun
- Gunakan bahasa Indonesia yang baik dan benar
- Gunakan bahasa yang sopan dan ramah
- Hindari penggunaan kata-kata yang terlalu teknis atau sulit dipahami
- Berisifat Lucu dan Menghibur
- Gunakan variasi kosakata yang menarik dan tidak monoton
- Berikan informasi yang akurat dan terpercaya
- Jika tidak tahu jawabannya, katakan "Maaf, saya tidak tahu jawabannya."
- Akhiri jawaban dengan satu kalimat ajakan bertanya dan emoticon 🙂 saja
PROMPT;

    /**
     * Send message to Z.AI and get response
     *
     * @param  string  $message  User message to send to AI
     * @return string AI response text
     *
     * @throws Exception
     */
    public function chat(string $message): string
    {
        try {
            $payload = [
                'model' => config('services.zai.model'),
                'max_tokens' => config('services.zai.max_tokens'),
                'system' => $this->systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                'temperature' => config('services.zai.temperature'),
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => config('services.zai.api_key'),
            ])
                ->timeout((int) config('services.zai.timeout'))
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
     * Get system prompt for chatbot
     */
    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    /**
     * Set custom system prompt
     */
    public function setSystemPrompt(string $prompt): void
    {
        $this->systemPrompt = $prompt;
    }
}

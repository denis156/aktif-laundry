<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatbotSetting>
 */
class ChatbotSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Chatbot Setting',
            'system_prompt' => <<<'PROMPT'
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
PROMPT,
            'is_active' => false,
            'enable_chatbot' => true,
            'max_tokens' => 65536,
            'temperature' => 0.70,
            'timeout' => 25,
            'fallback_message' => 'Maaf, saya sedang mengalami gangguan teknis. Mohon coba lagi dalam beberapa saat. 🙏',
        ];
    }

    /**
     * Mark this setting as active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Mark chatbot as disabled
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_chatbot' => false,
        ]);
    }
}

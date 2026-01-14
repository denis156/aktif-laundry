<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Chatbot;

use App\Models\ChatbotSetting;
use App\Services\ZaiService;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Pengaturan Chatbot')]
#[Layout('layouts.management.app')]
class Pengaturan extends Component
{
    use Toast;

    public string $name = '';

    public string $system_prompt = '';

    public bool $is_active = true;

    public bool $enable_chatbot = true;

    public int $max_tokens = 65536;

    public float $temperature = 0.70;

    public int $timeout = 25;

    public string $fallback_message = '';

    private array $originalValues = [];

    public function mount(): void
    {
        $this->loadSettings();
        $this->saveOriginalValues();
    }

    protected function loadSettings(): void
    {
        $setting = ChatbotSetting::getActive() ?? ChatbotSetting::query()->first();

        if ($setting) {
            $this->name = $setting->name;
            $this->system_prompt = $setting->system_prompt;
            $this->is_active = $setting->is_active;
            $this->enable_chatbot = $setting->enable_chatbot;
            $this->max_tokens = $setting->max_tokens;
            $this->temperature = $setting->temperature;
            $this->timeout = $setting->timeout;
            $this->fallback_message = $setting->fallback_message;
        } else {
            // Set defaults if no setting exists
            $this->name = 'Pengaturan Chatbot Default';
            $this->system_prompt = <<<'PROMPT'
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
            $this->fallback_message = 'Maaf, saya sedang mengalami gangguan teknis. Mohon coba lagi dalam beberapa saat. 🙏';
        }
    }

    protected function saveOriginalValues(): void
    {
        $this->originalValues = [
            'name' => $this->name,
            'system_prompt' => $this->system_prompt,
            'is_active' => $this->is_active,
            'enable_chatbot' => $this->enable_chatbot,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'timeout' => $this->timeout,
            'fallback_message' => $this->fallback_message,
        ];
    }

    public function hasChanges(): bool
    {
        if (empty($this->originalValues)) {
            return false;
        }

        return $this->name !== ($this->originalValues['name'] ?? '')
            || $this->system_prompt !== ($this->originalValues['system_prompt'] ?? '')
            || $this->is_active !== ($this->originalValues['is_active'] ?? true)
            || $this->enable_chatbot !== ($this->originalValues['enable_chatbot'] ?? true)
            || $this->max_tokens !== ($this->originalValues['max_tokens'] ?? 65536)
            || $this->temperature !== ($this->originalValues['temperature'] ?? 0.70)
            || $this->timeout !== ($this->originalValues['timeout'] ?? 25)
            || $this->fallback_message !== ($this->originalValues['fallback_message'] ?? '');
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'system_prompt' => ['required', 'string', 'min:10'],
            'is_active' => ['boolean'],
            'enable_chatbot' => ['boolean'],
            'max_tokens' => ['required', 'integer', 'min:100', 'max:100000'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'fallback_message' => ['required', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pengaturan wajib diisi',
            'system_prompt.required' => 'System prompt wajib diisi',
            'system_prompt.min' => 'System prompt minimal 10 karakter',
            'max_tokens.required' => 'Max tokens wajib diisi',
            'max_tokens.min' => 'Max tokens minimal 100',
            'max_tokens.max' => 'Max tokens maksimal 100000',
            'temperature.required' => 'Temperature wajib diisi',
            'temperature.min' => 'Temperature minimal 0',
            'temperature.max' => 'Temperature maksimal 2',
            'timeout.required' => 'Timeout wajib diisi',
            'timeout.min' => 'Timeout minimal 5 detik',
            'timeout.max' => 'Timeout maksimal 120 detik',
            'fallback_message.required' => 'Fallback message wajib diisi',
        ]);

        try {
            DB::beginTransaction();

            $setting = ChatbotSetting::getActive() ?? ChatbotSetting::query()->first();

            if ($setting) {
                $setting->update([
                    'name' => $this->name,
                    'system_prompt' => $this->system_prompt,
                    'is_active' => $this->is_active,
                    'enable_chatbot' => $this->enable_chatbot,
                    'max_tokens' => $this->max_tokens,
                    'temperature' => $this->temperature,
                    'timeout' => $this->timeout,
                    'fallback_message' => $this->fallback_message,
                ]);
            } else {
                ChatbotSetting::query()->create([
                    'name' => $this->name,
                    'system_prompt' => $this->system_prompt,
                    'is_active' => $this->is_active,
                    'enable_chatbot' => $this->enable_chatbot,
                    'max_tokens' => $this->max_tokens,
                    'temperature' => $this->temperature,
                    'timeout' => $this->timeout,
                    'fallback_message' => $this->fallback_message,
                ]);
            }

            // Clear cache after saving
            ZaiService::clearCache();

            DB::commit();

            $this->saveOriginalValues();

            $this->success('Pengaturan chatbot berhasil disimpan!', position: 'toast-bottom');
        } catch (Exception $e) {
            DB::rollBack();

            $this->error(
                'Gagal menyimpan pengaturan: '.$e->getMessage(),
                position: 'toast-bottom'
            );
        }
    }

    public function resetChanges(): void
    {
        if (empty($this->originalValues)) {
            $this->warning('Tidak ada perubahan untuk direset', position: 'toast-bottom');

            return;
        }

        $this->name = $this->originalValues['name'] ?? '';
        $this->system_prompt = $this->originalValues['system_prompt'] ?? '';
        $this->is_active = $this->originalValues['is_active'] ?? true;
        $this->enable_chatbot = $this->originalValues['enable_chatbot'] ?? true;
        $this->max_tokens = $this->originalValues['max_tokens'] ?? 65536;
        $this->temperature = $this->originalValues['temperature'] ?? 0.70;
        $this->timeout = $this->originalValues['timeout'] ?? 25;
        $this->fallback_message = $this->originalValues['fallback_message'] ?? '';

        $this->success('Perubahan dibatalkan', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.chatbot.pengaturan', [
            'hasChanges' => $this->hasChanges(),
        ]);
    }
}

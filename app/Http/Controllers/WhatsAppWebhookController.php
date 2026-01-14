<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FonnteService;
use App\Services\ZaiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle incoming WhatsApp webhook from Fonnte
     */
    public function __construct(
        protected ZaiService $zaiService,
        protected FonnteService $fonnteService
    ) {}

    /**
     * Handle incoming webhook message
     */
    public function handle(Request $request): JsonResponse
    {
        // Validate incoming webhook data
        $request->validate([
            'sender' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $from = $request->input('sender');
        $text = trim($request->input('message', ''));

        // Skip if message is empty after trimming
        if ($text === '') {
            return response()->json(['status' => 'ignored', 'reason' => 'empty_message'], 200);
        }

        try {
            // Get AI response from Z.AI
            $aiResponse = $this->zaiService->chat($text);

            // Send response back via WhatsApp
            $this->fonnteService->sendMessage($from, $aiResponse);

            Log::info('WhatsApp chatbot message processed', [
                'from' => $from,
                'message_length' => strlen($text),
                'response_length' => strlen($aiResponse),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Message processed and reply sent',
            ], 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error: '.$e->getMessage(), [
                'from' => $from,
                'message' => $text,
                'exception' => $e,
            ]);

            // Send fallback message to user
            $fallbackMessage = 'Maaf, saya sedang mengalami gangguan teknis. Mohon coba lagi dalam beberapa saat. 🙏';

            try {
                $this->fonnteService->sendMessage($from, $fallbackMessage);
            } catch (\Exception $fallbackException) {
                Log::error('Failed to send fallback message: '.$fallbackException->getMessage());
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

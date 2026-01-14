<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\ChatbotHelper;
use App\Helper\ConversationHelper;
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
        $messageType = $request->input('type', 'text'); // location, text, image, etc

        // Skip if message is empty after trimming
        if ($text === '') {
            return response()->json(['status' => 'ignored', 'reason' => 'empty_message'], 200);
        }

        // Skip WhatsApp group messages (groups have @g.us suffix)
        if (str_contains($from, '@g.us')) {
            Log::info('WhatsApp group message ignored', [
                'from' => $from,
                'message_length' => strlen($text),
            ]);

            return response()->json(['status' => 'ignored', 'reason' => 'group_message'], 200);
        }

        try {
            // Process message with conversation flow
            $conversationResult = ConversationHelper::processMessage($from, $text, $messageType);

            // If conversation helper returned a direct response, send it
            if (! $conversationResult['should_use_ai'] && $conversationResult['response']) {
                $this->fonnteService->sendMessage($from, $conversationResult['response']);

                Log::info('WhatsApp conversation flow message processed', [
                    'from' => $from,
                    'message_length' => strlen($text),
                    'response_length' => strlen($conversationResult['response']),
                    'flow_step' => $conversationResult['session']?->current_step ?? 'none',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Conversation flow message sent',
                    'flow_step' => $conversationResult['session']?->current_step ?? 'none',
                ], 200);
            }

            // Otherwise, use AI for response (existing customer or inquiry flow)
            // Build context with database information
            $context = ChatbotHelper::buildContext($from);

            // Get AI response from Z.AI with context
            $aiResponse = $this->zaiService->chat($text, $context);

            // Send response back via WhatsApp
            $this->fonnteService->sendMessage($from, $aiResponse);

            Log::info('WhatsApp AI chatbot message processed', [
                'from' => $from,
                'message_length' => strlen($text),
                'response_length' => strlen($aiResponse),
                'context_length' => strlen($context),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'AI message processed and reply sent',
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

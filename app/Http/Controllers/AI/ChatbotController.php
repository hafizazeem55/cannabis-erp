<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\CultivationChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    protected CultivationChatbotService $chatbotService;

    public function __construct(CultivationChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Send chat message
     */
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:2000',
            'batch_id' => 'nullable|exists:batches,id',
            'conversation_history' => 'nullable|array',
            'conversation_history.*.query' => 'required|string',
            'conversation_history.*.response' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $chatLog = $this->chatbotService->chat(
                $request->query,
                auth()->id(),
                $request->batch_id,
                auth()->user()->organization_id,
                $request->conversation_history ?? []
            );

            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chatLog->id,
                    'query' => $chatLog->query,
                    'response' => $chatLog->response,
                    'context_snapshot' => $chatLog->context_snapshot,
                    'response_time' => $chatLog->response_time_seconds,
                    'created_at' => $chatLog->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream chat response
     */
    public function streamChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:2000',
            'batch_id' => 'nullable|exists:batches,id',
            'conversation_history' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->stream(function () use ($request) {
            try {
                foreach ($this->chatbotService->streamChat(
                    $request->query,
                    auth()->id(),
                    $request->batch_id,
                    $request->conversation_history ?? []
                ) as $chunk) {
                    echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get conversation history
     */
    public function history(Request $request)
    {
        $batchId = $request->query('batch_id');
        $limit = $request->query('limit', 20);

        $history = $this->chatbotService->getConversationHistory(
            auth()->id(),
            $batchId,
            $limit
        );

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Provide feedback on chat
     */
    public function feedback(Request $request, int $chatId)
    {
        $validator = Validator::make($request->all(), [
            'was_helpful' => 'required|boolean',
            'feedback' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->chatbotService->provideFeedback(
            $chatId,
            $request->was_helpful,
            $request->feedback
        );

        return response()->json([
            'success' => $success,
        ]);
    }

    /**
     * Get user chat statistics
     */
    public function stats()
    {
        $stats = $this->chatbotService->getChatStats(auth()->id());

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}

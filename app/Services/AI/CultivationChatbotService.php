<?php

namespace App\Services\AI;

use App\Models\AiChatLog;
use Illuminate\Support\Facades\Log;

class CultivationChatbotService
{
    protected AIProviderInterface $provider;
    protected RetrieverService $retriever;

    public function __construct(RetrieverService $retriever)
    {
        $this->provider = app(OpenAIProvider::class);
        $this->retriever = $retriever;
    }

    /**
     * Generate chat response with RAG
     */
    public function chat(
        string $query,
        int $userId,
        ?int $batchId = null,
        ?int $organizationId = null,
        array $conversationHistory = []
    ): AiChatLog {
        $startTime = microtime(true);

        // Retrieve relevant context
        $topK = config('ai.rag.top_k_results');
        $retrievedChunks = $this->retriever->retrieveContext($query, $batchId, $topK);

        // Get specific batch context if provided
        $batchContext = $batchId ? $this->retriever->getBatchContext($batchId) : '';

        // Build enhanced prompt
        $enhancedPrompt = $this->buildEnhancedPrompt(
            $query,
            $retrievedChunks,
            $batchContext,
            $conversationHistory
        );

        // Generate response
        $result = $this->provider->complete($enhancedPrompt);

        if (!$result['success']) {
            throw new \Exception("Chat failed: {$result['error']}");
        }

        // Store chat log
        $chatLog = AiChatLog::create([
            'user_id' => $userId,
            'batch_id' => $batchId,
            'organization_id' => $organizationId,
            'query' => $query,
            'response' => $result['content'],
            'context_used' => $retrievedChunks,
            'embeddings_ref' => array_column($retrievedChunks, 'metadata'),
            'context_snapshot' => $this->retriever->getContextSummary($retrievedChunks),
            'provider' => $this->provider->getProviderName(),
            'tokens_used' => $result['usage']['total_tokens'] ?? null,
            'response_time_seconds' => microtime(true) - $startTime,
        ]);

        if (config('ai.logging.enabled')) {
            Log::info('AI Chat', [
                'chat_id' => $chatLog->id,
                'user_id' => $userId,
                'batch_id' => $batchId,
                'context_chunks' => count($retrievedChunks),
            ]);
        }

        return $chatLog;
    }

    /**
     * Stream chat response
     */
    public function streamChat(
        string $query,
        int $userId,
        ?int $batchId = null,
        array $conversationHistory = []
    ): \Generator {
        // Retrieve context
        $retrievedChunks = $this->retriever->retrieveContext($query, $batchId);
        $batchContext = $batchId ? $this->retriever->getBatchContext($batchId) : '';

        // Build prompt
        $enhancedPrompt = $this->buildEnhancedPrompt(
            $query,
            $retrievedChunks,
            $batchContext,
            $conversationHistory
        );

        // Stream response
        $fullResponse = '';
        foreach ($this->provider->streamComplete($enhancedPrompt) as $chunk) {
            $fullResponse .= $chunk;
            yield $chunk;
        }

        // Store after streaming completes
        AiChatLog::create([
            'user_id' => $userId,
            'batch_id' => $batchId,
            'query' => $query,
            'response' => $fullResponse,
            'context_used' => $retrievedChunks,
            'context_snapshot' => $this->retriever->getContextSummary($retrievedChunks),
            'provider' => $this->provider->getProviderName(),
        ]);
    }

    /**
     * Build enhanced prompt with RAG context
     */
    protected function buildEnhancedPrompt(
        string $query,
        array $retrievedChunks,
        string $batchContext,
        array $conversationHistory
    ): string {
        $systemPrompt = config('ai.chatbot.system_prompt');
        
        $prompt = "{$systemPrompt}\n\n";

        // Add retrieved context
        if (!empty($retrievedChunks)) {
            $contextString = $this->retriever->buildContextString($retrievedChunks);
            $prompt .= "{$contextString}\n\n";
        }

        // Add specific batch context
        if ($batchContext) {
            $prompt .= "{$batchContext}\n\n";
        }

        // Add conversation history
        if (!empty($conversationHistory)) {
            $prompt .= "=== CONVERSATION HISTORY ===\n";
            foreach ($conversationHistory as $msg) {
                $prompt .= "User: {$msg['query']}\n";
                $prompt .= "Assistant: {$msg['response']}\n\n";
            }
        }

        // Add current query
        $prompt .= "=== CURRENT QUESTION ===\n";
        $prompt .= "{$query}\n\n";
        $prompt .= "Please provide a detailed, accurate answer based on the cultivation data provided above. ";
        $prompt .= "Reference specific batch codes, dates, and measurements when relevant. ";
        $prompt .= "If the data doesn't contain enough information to answer fully, say so clearly.";

        return $prompt;
    }

    /**
     * Get conversation history for user
     */
    public function getConversationHistory(int $userId, ?int $batchId = null, int $limit = 10): array
    {
        $query = AiChatLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        return $query->get()->map(function($log) {
            return [
                'query' => $log->query,
                'response' => $log->response,
                'created_at' => $log->created_at,
            ];
        })->toArray();
    }

    /**
     * Mark chat as helpful/not helpful
     */
    public function provideFeedback(int $chatLogId, bool $wasHelpful, ?string $feedback = null): bool
    {
        $chatLog = AiChatLog::find($chatLogId);
        
        if (!$chatLog) {
            return false;
        }

        $chatLog->update([
            'was_helpful' => $wasHelpful,
            'feedback' => $feedback,
        ]);

        return true;
    }

    /**
     * Get chat statistics
     */
    public function getChatStats(int $userId): array
    {
        $logs = AiChatLog::where('user_id', $userId)->get();

        return [
            'total_chats' => $logs->count(),
            'helpful_count' => $logs->where('was_helpful', true)->count(),
            'avg_response_time' => round($logs->avg('response_time_seconds'), 2),
            'total_tokens_used' => $logs->sum('tokens_used'),
        ];
    }
}

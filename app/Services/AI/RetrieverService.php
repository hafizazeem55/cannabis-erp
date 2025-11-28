<?php

namespace App\Services\AI;

use App\Models\AiEmbedding;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Strain;
use App\Models\Room;
use App\Models\EnvironmentalReading;
use App\Models\Harvest;
use App\Models\Facility;
use App\Models\GrowthCycle;
use Illuminate\Support\Facades\Log;

class RetrieverService
{
    protected AIProviderInterface $provider;

    public function __construct()
    {
        $this->provider = app(OpenAIProvider::class);
    }

    /**
     * Retrieve relevant context for a query
     */
    public function retrieveContext(string $query, ?int $batchId = null, int $topK = 10): array
    {
        // Generate query embedding
        $result = $this->provider->generateEmbeddings($query);

        if (!$result['success'] || empty($result['embeddings'])) {
            Log::warning('Failed to generate query embedding');
            return [];
        }

        $queryVector = $result['embeddings'][0];

        // Find similar embeddings
        $threshold = config('ai.rag.similarity_threshold');
        $similarEmbeddings = AiEmbedding::findSimilar($queryVector, $topK, $threshold);

        // If batch_id is provided, prioritize batch-related content
        if ($batchId) {
            $similarEmbeddings = $this->prioritizeBatchContext($similarEmbeddings, $batchId);
        }

        return array_map(function($item) {
            return [
                'content' => $item['embedding']->content,
                'metadata' => $item['embedding']->metadata,
                'similarity' => $item['similarity'],
                'source' => $item['embedding']->source_table,
            ];
        }, $similarEmbeddings);
    }

    /**
     * Prioritize batch-related context
     */
    protected function prioritizeBatchContext(array $embeddings, int $batchId): array
    {
        $batchRelated = [];
        $others = [];

        foreach ($embeddings as $embedding) {
            $metadata = $embedding['embedding']->metadata ?? [];
            
            if (isset($metadata['batch_id']) && $metadata['batch_id'] == $batchId) {
                $embedding['similarity'] += 0.1; // Boost batch-related content
                $batchRelated[] = $embedding;
            } else {
                $others[] = $embedding;
            }
        }

        return array_merge($batchRelated, $others);
    }

    /**
     * Build context from retrieved chunks
     */
    public function buildContextString(array $retrievedChunks): string
    {
        $context = "=== RELEVANT CULTIVATION DATA ===\n\n";

        foreach ($retrievedChunks as $i => $chunk) {
            $source = strtoupper(str_replace('_', ' ', $chunk['source']));
            $similarity = round($chunk['similarity'] * 100);
            
            $context .= "[SOURCE {$source} - {$similarity}% relevant]\n";
            $context .= $chunk['content'];
            $context .= "\n\n" . str_repeat('-', 50) . "\n\n";
        }

        return $context;
    }

    /**
     * Get specific batch context
     */
    public function getBatchContext(int $batchId): string
    {
        $batch = Batch::with(['strain', 'room', 'growthCycle', 'logs', 'harvests'])->find($batchId);

        if (!$batch) {
            return '';
        }

        $context = "=== BATCH DETAILS ===\n";
        $context .= "Batch Code: {$batch->batch_code}\n";
        $context .= "Strain: {$batch->strain->name} ({$batch->strain->type})\n";
        $context .= "Status: {$batch->status}\n";
        $context .= "Room: {$batch->room->name} ({$batch->room->type})\n";
        $context .= "Plant Count: {$batch->current_plant_count}/{$batch->initial_plant_count}\n";
        $context .= "Progress: {$batch->progress_percentage}%\n";
        $context .= "Planting Date: {$batch->planting_date->format('Y-m-d')}\n";

        if ($batch->expected_harvest_date) {
            $context .= "Expected Harvest: {$batch->expected_harvest_date->format('Y-m-d')}\n";
        }

        // Recent logs
        $recentLogs = $batch->logs()->orderBy('log_date', 'desc')->take(5)->get();
        if ($recentLogs->isNotEmpty()) {
            $context .= "\nRECENT ACTIVITIES:\n";
            foreach ($recentLogs as $log) {
                $context .= "- {$log->log_date->format('Y-m-d')}: ";
                $activities = is_array($log->activities) ? $log->activities : [];
                $context .= implode(', ', array_column($activities, 'activity'));
                $context .= "\n";
            }
        }

        return $context;
    }

    /**
     * Search knowledge base by keywords
     */
    public function searchByKeywords(string $keywords, int $limit = 10): array
    {
        $results = [];

        // Search in multiple tables
        $sources = config('ai.rag.data_sources');

        if ($sources['batches']) {
            $batches = Batch::where('batch_code', 'like', "%{$keywords}%")
                ->orWhere('notes', 'like', "%{$keywords}%")
                ->limit($limit)
                ->get();
            
            foreach ($batches as $batch) {
                $results[] = [
                    'type' => 'batch',
                    'content' => "Batch {$batch->batch_code}: {$batch->status}",
                    'data' => $batch,
                ];
            }
        }

        if ($sources['strains']) {
            $strains = Strain::where('name', 'like', "%{$keywords}%")
                ->orWhere('description', 'like', "%{$keywords}%")
                ->limit($limit)
                ->get();
            
            foreach ($strains as $strain) {
                $results[] = [
                    'type' => 'strain',
                    'content' => "Strain {$strain->name}: {$strain->type}",
                    'data' => $strain,
                ];
            }
        }

        return $results;
    }

    /**
     * Get context summary for UI
     */
    public function getContextSummary(array $retrievedChunks): string
    {
        $sources = array_unique(array_column($retrievedChunks, 'source'));
        $count = count($retrievedChunks);
        
        return "Used {$count} knowledge chunks from: " . implode(', ', $sources);
    }
}

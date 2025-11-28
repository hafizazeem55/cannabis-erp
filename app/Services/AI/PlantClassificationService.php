<?php

namespace App\Services\AI;

use App\Models\AiClassificationResult;
use Illuminate\Support\Facades\Log;

class PlantClassificationService
{
    protected AIProviderInterface $provider;

    public function __construct()
    {
        $providerName = config('ai.provider');
        $this->provider = $providerName === 'local' 
            ? app(LocalVisionProvider::class)
            : app(OpenAIProvider::class);
    }

    /**
     * Classify plant image
     */
    public function classify(
        string $imagePath,
        ?int $batchId = null,
        ?int $roomId = null,
        ?int $userId = null
    ): AiClassificationResult {
        $startTime = microtime(true);

        // Generate classification prompt
        $prompt = $this->buildClassificationPrompt();

        // Analyze image
        $result = $this->provider->analyzeImage($imagePath, $prompt, ['task' => 'classification']);

        if (!$result['success']) {
            throw new \Exception("Classification failed: {$result['error']}");
        }

        // Parse AI response
        $classification = $this->parseClassificationResponse($result['content']);

        // Store result
        $classificationResult = AiClassificationResult::create([
            'batch_id' => $batchId,
            'room_id' => $roomId,
            'image_path' => $imagePath,
            'classifications' => $classification['classifications'],
            'top_label' => $classification['top_label'],
            'top_category' => $classification['top_category'],
            'confidence' => $classification['confidence'],
            'growth_stage' => $classification['growth_stage'],
            'health_status' => $classification['health_status'],
            'leaf_issues' => $classification['leaf_issues'],
            'strain_type_prediction' => $classification['strain_type'],
            'provider' => $this->provider->getProviderName(),
            'raw_response' => $result['raw_response'] ?? [],
            'metadata' => [
                'processing_time' => microtime(true) - $startTime,
                'model' => $result['model'] ?? null,
            ],
            'created_by' => $userId,
        ]);

        // Log for analytics
        if (config('ai.logging.enabled')) {
            Log::info('AI Plant Classification', [
                'result_id' => $classificationResult->id,
                'batch_id' => $batchId,
                'top_label' => $classification['top_label'],
                'confidence' => $classification['confidence'],
            ]);
        }

        return $classificationResult;
    }

    /**
     * Build classification prompt
     */
    protected function buildClassificationPrompt(): string
    {
        $categories = config('ai.classification.categories');

        return <<<PROMPT
You are an expert cannabis plant classifier. Analyze this plant image and classify it across multiple categories.

Classify the plant in these categories:
1. Growth Stage: {$this->formatArray($categories['growth_stage'])}
2. Health Status: {$this->formatArray($categories['health_status'])}
3. Leaf Issues: {$this->formatArray($categories['leaf_issues'])}
4. Strain Type: {$this->formatArray($categories['strain_type'])}

Respond in JSON format:
{
    "growth_stage": {
        "label": "flowering",
        "confidence": 0.95
    },
    "health_status": {
        "label": "healthy",
        "confidence": 0.88
    },
    "leaf_issues": {
        "labels": ["healthy"],
        "confidence": 0.92
    },
    "strain_type": {
        "label": "indica",
        "confidence": 0.75
    },
    "observations": [
        "Dense bud formation visible",
        "Trichome production is abundant"
    ]
}

Provide confidence scores between 0.0 and 1.0 for each classification.
PROMPT;
    }

    /**
     * Parse classification response
     */
    protected function parseClassificationResponse(string $response): array
    {
        try {
            // Extract JSON from response
            if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $response, $matches)) {
                $json = json_decode($matches[0], true);
                
                if ($json) {
                    $classifications = [];
                    $topConfidence = 0;
                    $topLabel = null;
                    $topCategory = null;

                    foreach ($json as $category => $data) {
                        if (is_array($data) && isset($data['label'])) {
                            $confidence = (float) ($data['confidence'] ?? 0.0);
                            $classifications[] = [
                                'category' => $category,
                                'label' => $data['label'],
                                'confidence' => $confidence,
                            ];

                            if ($confidence > $topConfidence) {
                                $topConfidence = $confidence;
                                $topLabel = $data['label'];
                                $topCategory = $category;
                            }
                        }
                    }

                    return [
                        'classifications' => $classifications,
                        'top_label' => $topLabel,
                        'top_category' => $topCategory,
                        'confidence' => $topConfidence,
                        'growth_stage' => $json['growth_stage']['label'] ?? null,
                        'health_status' => $json['health_status']['label'] ?? null,
                        'leaf_issues' => $json['leaf_issues']['labels'] ?? [],
                        'strain_type' => $json['strain_type']['label'] ?? null,
                    ];
                }
            }

            return $this->parseTextClassification($response);
        } catch (\Exception $e) {
            Log::error('Failed to parse classification response', [
                'response' => substr($response, 0, 500),
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultClassification();
        }
    }

    /**
     * Parse text classification as fallback
     */
    protected function parseTextClassification(string $response): array
    {
        return [
            'classifications' => [
                ['category' => 'general', 'label' => 'unclassified', 'confidence' => 0.5]
            ],
            'top_label' => 'unclassified',
            'top_category' => 'general',
            'confidence' => 0.5,
            'growth_stage' => null,
            'health_status' => null,
            'leaf_issues' => [],
            'strain_type' => null,
        ];
    }

    /**
     * Get default classification
     */
    protected function getDefaultClassification(): array
    {
        return [
            'classifications' => [],
            'top_label' => null,
            'top_category' => null,
            'confidence' => 0.0,
            'growth_stage' => null,
            'health_status' => null,
            'leaf_issues' => [],
            'strain_type' => null,
        ];
    }

    /**
     * Format array for prompt
     */
    protected function formatArray(array $items): string
    {
        return implode(', ', $items);
    }

    /**
     * Get classification statistics for batch
     */
    public function getBatchClassificationStats(int $batchId): array
    {
        $results = AiClassificationResult::where('batch_id', $batchId)->get();

        return [
            'total_classifications' => $results->count(),
            'by_growth_stage' => $results->groupBy('growth_stage')->map->count(),
            'by_health_status' => $results->groupBy('health_status')->map->count(),
            'avg_confidence' => round($results->avg('confidence'), 2),
            'high_confidence_count' => $results->filter(fn($r) => $r->confidence >= 0.8)->count(),
        ];
    }
}

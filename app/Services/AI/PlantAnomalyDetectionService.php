<?php

namespace App\Services\AI;

use App\Models\AiAnomalyReport;
use App\Models\Batch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PlantAnomalyDetectionService
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
     * Detect anomalies in plant image
     */
    public function detectAnomaly(
        string $imagePath,
        int $batchId,
        ?int $roomId = null,
        ?int $userId = null
    ): AiAnomalyReport {
        $startTime = microtime(true);

        // Generate prompt for anomaly detection
        $prompt = $this->buildAnomalyDetectionPrompt();

        // Analyze image
        $result = $this->provider->analyzeImage($imagePath, $prompt, ['task' => 'anomaly']);

        if (!$result['success']) {
            throw new \Exception("Anomaly detection failed: {$result['error']}");
        }

        // Parse AI response
        $analysis = $this->parseAnomalyResponse($result['content']);

        // Store result
        $report = AiAnomalyReport::create([
            'batch_id' => $batchId,
            'room_id' => $roomId,
            'image_path' => $imagePath,
            'is_anomaly' => $analysis['is_anomaly'],
            'confidence' => $analysis['confidence'],
            'detected_issue' => $analysis['detected_issue'],
            'issue_description' => $analysis['issue_description'],
            'recommended_action' => $analysis['recommended_action'],
            'severity' => $analysis['severity'],
            'provider' => $this->provider->getProviderName(),
            'raw_response' => $result['raw_response'] ?? [],
            'metadata' => [
                'processing_time' => microtime(true) - $startTime,
                'model' => $result['model'] ?? null,
            ],
            'created_by' => $userId,
        ]);

        // Log for audit
        if (config('ai.logging.enabled')) {
            Log::info('AI Anomaly Detection', [
                'report_id' => $report->id,
                'batch_id' => $batchId,
                'is_anomaly' => $analysis['is_anomaly'],
                'confidence' => $analysis['confidence'],
            ]);
        }

        return $report;
    }

    /**
     * Batch process multiple images
     */
    public function detectAnomaliesInBatch(array $images, int $batchId, ?int $userId = null): array
    {
        $results = [];

        foreach ($images as $imagePath) {
            try {
                $results[] = $this->detectAnomaly($imagePath, $batchId, null, $userId);
            } catch (\Exception $e) {
                Log::error('Batch anomaly detection error', [
                    'image' => $imagePath,
                    'error' => $e->getMessage(),
                ]);
                $results[] = ['error' => $e->getMessage(), 'image' => $imagePath];
            }
        }

        return $results;
    }

    /**
     * Build anomaly detection prompt
     */
    protected function buildAnomalyDetectionPrompt(): string
    {
        $issueTypes = implode(', ', config('ai.anomaly_detection.issue_types'));

        return <<<PROMPT
You are an expert cannabis plant health inspector. Analyze this plant image and detect any anomalies or issues.

Look for these specific issues:
{$issueTypes}

Respond in JSON format with the following structure:
{
    "is_anomaly": true/false,
    "confidence": 0.0 to 1.0,
    "detected_issue": "specific issue name or null",
    "issue_description": "detailed description of the issue",
    "severity": "low|medium|high|critical",
    "recommended_action": "specific recommendations",
    "observations": [
        "observation 1",
        "observation 2"
    ]
}

Be specific and actionable in your recommendations.
PROMPT;
    }

    /**
     * Parse AI response
     */
    protected function parseAnomalyResponse(string $response): array
    {
        try {
            // Try to extract JSON from response
            if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $response, $matches)) {
                $json = json_decode($matches[0], true);
                
                if ($json) {
                    return [
                        'is_anomaly' => $json['is_anomaly'] ?? false,
                        'confidence' => (float) ($json['confidence'] ?? 0.0),
                        'detected_issue' => $json['detected_issue'] ?? null,
                        'issue_description' => $json['issue_description'] ?? '',
                        'severity' => $json['severity'] ?? 'low',
                        'recommended_action' => $json['recommended_action'] ?? '',
                    ];
                }
            }

            // Fallback: Parse text response
            return $this->parseTextResponse($response);
        } catch (\Exception $e) {
            Log::error('Failed to parse anomaly response', [
                'response' => substr($response, 0, 500),
                'error' => $e->getMessage(),
            ]);

            return [
                'is_anomaly' => false,
                'confidence' => 0.0,
                'detected_issue' => null,
                'issue_description' => 'Failed to parse response',
                'severity' => 'low',
                'recommended_action' => 'Manual inspection required',
            ];
        }
    }

    /**
     * Parse text response as fallback
     */
    protected function parseTextResponse(string $response): array
    {
        $isAnomaly = str_contains(strtolower($response), 'anomaly') 
                  || str_contains(strtolower($response), 'issue')
                  || str_contains(strtolower($response), 'problem');

        return [
            'is_anomaly' => $isAnomaly,
            'confidence' => 0.7,
            'detected_issue' => 'Unknown',
            'issue_description' => substr($response, 0, 500),
            'severity' => 'medium',
            'recommended_action' => 'Review AI analysis and consult cultivation expert',
        ];
    }

    /**
     * Get anomaly statistics for batch
     */
    public function getBatchAnomalyStats(int $batchId): array
    {
        $reports = AiAnomalyReport::where('batch_id', $batchId)->get();

        return [
            'total_reports' => $reports->count(),
            'anomalies_detected' => $reports->where('is_anomaly', true)->count(),
            'by_severity' => [
                'critical' => $reports->where('severity', 'critical')->count(),
                'high' => $reports->where('severity', 'high')->count(),
                'medium' => $reports->where('severity', 'medium')->count(),
                'low' => $reports->where('severity', 'low')->count(),
            ],
            'unreviewed' => $reports->where('reviewed', false)->count(),
            'avg_confidence' => round($reports->avg('confidence'), 2),
        ];
    }
}

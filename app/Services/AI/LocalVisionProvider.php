<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocalVisionProvider implements AIProviderInterface
{
    protected string $anomalyEndpoint;
    protected string $classificationEndpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->anomalyEndpoint = config('ai.local.anomaly_endpoint');
        $this->classificationEndpoint = config('ai.local.classification_endpoint');
        $this->timeout = config('ai.local.timeout');
    }

    /**
     * Generate text completion (not supported in local vision provider)
     */
    public function complete(string $prompt, array $options = []): array
    {
        return [
            'success' => false,
            'error' => 'Text completion not supported by local vision provider',
            'content' => '',
        ];
    }

    /**
     * Analyze image using local PyTorch endpoint
     */
    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array
    {
        try {
            $endpoint = $options['task'] === 'classification' 
                ? $this->classificationEndpoint 
                : $this->anomalyEndpoint;

            $response = Http::timeout($this->timeout)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post($endpoint, [
                    'prompt' => $prompt,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'content' => json_encode($response->json()),
                    'raw_response' => $response->json(),
                ];
            }

            throw new \Exception("Local endpoint error: {$response->status()}");
        } catch (\Exception $e) {
            Log::error('Local vision provider error', [
                'error' => $e->getMessage(),
                'image' => $imagePath,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => '',
            ];
        }
    }

    /**
     * Generate embeddings (not supported)
     */
    public function generateEmbeddings(string|array $text): array
    {
        return [
            'success' => false,
            'error' => 'Embeddings not supported by local vision provider',
            'embeddings' => [],
        ];
    }

    /**
     * Stream completion (not supported)
     */
    public function streamComplete(string $prompt, array $options = []): \Generator
    {
        yield "Streaming not supported by local vision provider";
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'local';
    }

    /**
     * Check if provider is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->anomalyEndpoint . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

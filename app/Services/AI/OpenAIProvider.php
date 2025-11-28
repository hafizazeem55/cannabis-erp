<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->baseUrl = config('ai.openai.base_url');
        $this->timeout = config('ai.openai.timeout');
        $this->maxRetries = config('ai.openai.max_retries');
    }

    /**
     * Generate text completion
     */
    public function complete(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? config('ai.models.text');
        $temperature = $options['temperature'] ?? config('ai.models.temperature');
        $maxTokens = $options['max_tokens'] ?? config('ai.models.max_tokens');

        try {
            $response = $this->makeRequest('chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            return [
                'success' => true,
                'content' => $response['choices'][0]['message']['content'] ?? '',
                'usage' => $response['usage'] ?? null,
                'model' => $model,
                'raw_response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI completion error', [
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 200),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => '',
            ];
        }
    }

    /**
     * Analyze image using vision model
     */
    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? config('ai.models.vision');
        $maxTokens = $options['max_tokens'] ?? 1000;

        try {
            // Convert image to base64
            $imageData = $this->getImageData($imagePath);

            $response = $this->makeRequest('chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$imageData['mime']};base64,{$imageData['base64']}"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => $maxTokens,
            ]);

            return [
                'success' => true,
                'content' => $response['choices'][0]['message']['content'] ?? '',
                'usage' => $response['usage'] ?? null,
                'model' => $model,
                'raw_response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI vision error', [
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
     * Generate embeddings for text
     */
    public function generateEmbeddings(string|array $text): array
    {
        $model = config('ai.models.embedding');
        $input = is_array($text) ? $text : [$text];

        try {
            $response = $this->makeRequest('embeddings', [
                'model' => $model,
                'input' => $input,
            ]);

            $embeddings = [];
            foreach ($response['data'] ?? [] as $item) {
                $embeddings[] = $item['embedding'];
            }

            return [
                'success' => true,
                'embeddings' => $embeddings,
                'model' => $model,
                'usage' => $response['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI embeddings error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'embeddings' => [],
            ];
        }
    }

    /**
     * Stream text completion
     */
    public function streamComplete(string $prompt, array $options = []): \Generator
    {
        $model = $options['model'] ?? config('ai.models.text');
        $temperature = $options['temperature'] ?? config('ai.models.temperature');

        try {
            $stream = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withOptions(['stream' => true])
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => $temperature,
                'stream' => true,
            ]);

            foreach ($stream as $chunk) {
                if (strpos($chunk, 'data: ') === 0) {
                    $data = substr($chunk, 6);
                    if (trim($data) === '[DONE]') {
                        break;
                    }

                    $decoded = json_decode($data, true);
                    if (isset($decoded['choices'][0]['delta']['content'])) {
                        yield $decoded['choices'][0]['delta']['content'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('OpenAI streaming error', ['error' => $e->getMessage()]);
            yield "Error: {$e->getMessage()}";
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'openai';
    }

    /**
     * Check if provider is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Make HTTP request to OpenAI API
     */
    protected function makeRequest(string $endpoint, array $data): array
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/{$endpoint}", $data);

                if ($response->successful()) {
                    return $response->json();
                }

                throw new \Exception("OpenAI API error: {$response->status()} - {$response->body()}");
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                sleep(pow(2, $attempt)); // Exponential backoff
            }
        }

        throw new \Exception('Max retries exceeded');
    }

    /**
     * Get image data as base64
     */
    protected function getImageData(string $imagePath): array
    {
        // Check if it's a URL or local path
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageContent = file_get_contents($imagePath);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($imageContent);
        } else {
            // Handle storage disk path
            if (Storage::disk(config('ai.images.storage_disk'))->exists($imagePath)) {
                $imageContent = Storage::disk(config('ai.images.storage_disk'))->get($imagePath);
                $mime = Storage::disk(config('ai.images.storage_disk'))->mimeType($imagePath);
            } else {
                throw new \Exception("Image not found: {$imagePath}");
            }
        }

        return [
            'base64' => base64_encode($imageContent),
            'mime' => $mime,
        ];
    }
}

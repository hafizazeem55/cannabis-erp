<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate text completion
     *
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function complete(string $prompt, array $options = []): array;

    /**
     * Analyze image using vision model
     *
     * @param string $imagePath
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function analyzeImage(string $imagePath, string $prompt, array $options = []): array;

    /**
     * Generate embeddings for text
     *
     * @param string|array $text
     * @return array
     */
    public function generateEmbeddings(string|array $text): array;

    /**
     * Stream text completion (for chat)
     *
     * @param string $prompt
     * @param array $options
     * @return \Generator
     */
    public function streamComplete(string $prompt, array $options = []): \Generator;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if provider is available
     *
     * @return bool
     */
    public function isAvailable(): bool;
}

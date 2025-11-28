<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AiEmbedding extends Model
{
    protected $fillable = [
        'source_table',
        'source_id',
        'content_hash',
        'content',
        'embedding_vector',
        'metadata',
        'embedding_model',
        'vector_dimensions',
    ];

    protected $casts = [
        'embedding_vector' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate content hash for idempotency
     */
    public static function generateContentHash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Find or create embedding for content
     */
    public static function findOrCreateForContent(
        string $sourceTable,
        int $sourceId,
        string $content,
        array $vector,
        array $metadata = [],
        string $model = 'text-embedding-3-large'
    ): self {
        $hash = static::generateContentHash($content);

        return static::updateOrCreate(
            [
                'source_table' => $sourceTable,
                'source_id' => $sourceId,
                'content_hash' => $hash,
            ],
            [
                'content' => $content,
                'embedding_vector' => $vector,
                'metadata' => $metadata,
                'embedding_model' => $model,
                'vector_dimensions' => count($vector),
            ]
        );
    }

    /**
     * Scope by source
     */
    public function scopeForSource($query, string $table, int $id)
    {
        return $query->where('source_table', $table)
                     ->where('source_id', $id);
    }

    /**
     * Scope by table
     */
    public function scopeFromTable($query, string $table)
    {
        return $query->where('source_table', $table);
    }

    /**
     * Calculate cosine similarity with another vector
     * Note: For production, use a vector database like pgvector, Pinecone, or Weaviate
     */
    public function cosineSimilarity(array $vector): float
    {
        $a = $this->embedding_vector;
        $b = $vector;

        if (!is_array($a) || !is_array($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] ** 2;
            $magnitudeB += $b[$i] ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Find similar embeddings
     * Note: This is a naive implementation. For production, use proper vector search.
     */
    public static function findSimilar(array $queryVector, int $topK = 10, float $threshold = 0.7): array
    {
        $embeddings = static::all();
        $results = [];

        foreach ($embeddings as $embedding) {
            $similarity = $embedding->cosineSimilarity($queryVector);
            if ($similarity >= $threshold) {
                $results[] = [
                    'embedding' => $embedding,
                    'similarity' => $similarity,
                ];
            }
        }

        // Sort by similarity descending
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($results, 0, $topK);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLog extends Model
{
    protected $fillable = [
        'user_id',
        'batch_id',
        'organization_id',
        'query',
        'response',
        'context_used',
        'embeddings_ref',
        'context_snapshot',
        'provider',
        'tokens_used',
        'response_time_seconds',
        'was_helpful',
        'feedback',
        'metadata',
    ];

    protected $casts = [
        'context_used' => 'array',
        'embeddings_ref' => 'array',
        'metadata' => 'array',
        'response_time_seconds' => 'decimal:3',
        'was_helpful' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by batch
     */
    public function scopeForBatch($query, int $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope helpful conversations
     */
    public function scopeHelpful($query)
    {
        return $query->where('was_helpful', true);
    }

    /**
     * Get conversation context summary
     */
    public function getContextSummaryAttribute(): string
    {
        if ($this->context_snapshot) {
            return $this->context_snapshot;
        }

        if ($this->batch_id) {
            return "Batch: {$this->batch->batch_code}";
        }

        return 'General conversation';
    }

    /**
     * Calculate average response time for analytics
     */
    public static function averageResponseTime(): float
    {
        return static::query()
            ->whereNotNull('response_time_seconds')
            ->avg('response_time_seconds') ?? 0;
    }
}

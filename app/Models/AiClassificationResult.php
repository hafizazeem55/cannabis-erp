<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiClassificationResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'room_id',
        'image_path',
        'classifications',
        'top_label',
        'top_category',
        'confidence',
        'growth_stage',
        'health_status',
        'leaf_issues',
        'strain_type_prediction',
        'provider',
        'raw_response',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'classifications' => 'array',
        'leaf_issues' => 'array',
        'confidence' => 'decimal:4',
        'raw_response' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope by growth stage
     */
    public function scopeByGrowthStage($query, string $stage)
    {
        return $query->where('growth_stage', $stage);
    }

    /**
     * Scope by health status
     */
    public function scopeByHealthStatus($query, string $status)
    {
        return $query->where('health_status', $status);
    }

    /**
     * Check if classification is high confidence
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence >= 0.80;
    }

    /**
     * Get health status color for UI
     */
    public function getHealthStatusColorAttribute(): string
    {
        return match($this->health_status) {
            'healthy' => 'success',
            'stressed' => 'warning',
            'diseased' => 'danger',
            'dying' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get all predicted labels with scores
     */
    public function getPredictionsAttribute(): array
    {
        if (!is_array($this->classifications)) {
            return [];
        }

        return collect($this->classifications)
            ->sortByDesc('confidence')
            ->values()
            ->toArray();
    }
}

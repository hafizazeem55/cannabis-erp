<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiAnomalyReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'room_id',
        'image_path',
        'is_anomaly',
        'confidence',
        'detected_issue',
        'issue_description',
        'recommended_action',
        'severity',
        'provider',
        'raw_response',
        'metadata',
        'reviewed',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'created_by',
    ];

    protected $casts = [
        'is_anomaly' => 'boolean',
        'confidence' => 'decimal:4',
        'raw_response' => 'array',
        'metadata' => 'array',
        'reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
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

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for unreviewed anomalies
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('reviewed', false);
    }

    /**
     * Scope for anomalies only
     */
    public function scopeAnomaliesOnly($query)
    {
        return $query->where('is_anomaly', true);
    }

    /**
     * Scope by severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'gray',
        };
    }

    /**
     * Check if anomaly is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Check if high confidence
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence >= 0.85;
    }
}

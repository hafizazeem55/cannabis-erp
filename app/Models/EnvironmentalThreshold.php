<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentalThreshold extends Model
{
    protected $fillable = [
        'stage',
        'parameter',
        'min_value',
        'max_value',
        'target_value',
        'tolerance_percent',
        'severity',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'tolerance_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const STAGES = [
        'clone',
        'propagation',
        'vegetative',
        'flower',
        'harvest',
        'completed',
    ];

    public const PARAMETERS = [
        'temperature',
        'humidity',
        'co2',
        'ph',
        'ec',
    ];

    public const SEVERITIES = [
        'standard',
        'warning',
        'critical',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeForParameter($query, string $parameter)
    {
        return $query->where('parameter', $parameter);
    }
}

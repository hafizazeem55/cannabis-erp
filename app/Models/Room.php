<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id',
        'name',
        'code',
        'type',
        'capacity',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'co2_min',
        'co2_max',
        'ph_min',
        'ph_max',
        'ec_min',
        'ec_max',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'humidity_min' => 'decimal:2',
        'humidity_max' => 'decimal:2',
        'co2_min' => 'decimal:2',
        'co2_max' => 'decimal:2',
        'ph_min' => 'decimal:2',
        'ph_max' => 'decimal:2',
        'ec_min' => 'decimal:2',
        'ec_max' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('is_active', true)
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function batchLogs(): HasMany
    {
        return $this->hasMany(BatchLog::class);
    }

    public function getCurrentUtilizationAttribute(): int
    {
        return $this->activeBatches()->sum('current_plant_count');
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->capacity == 0) {
            return 0;
        }
        return ($this->current_utilization / $this->capacity) * 100;
    }

    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_utilization);
    }
}


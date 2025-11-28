<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    protected $fillable = [
        'batch_id',
        'room_id',
        'harvest_date',
        'harvest_time',
        'wet_weight',
        'trim_weight',
        'waste_weight',
        'dry_weight',
        'harvested_plant_count',
        'expected_yield',
        'actual_yield',
        'yield_percentage',
        'quality_notes',
        'harvest_notes',
        'status',
        'low_yield_deviation_raised',
        'lots_created',
        'harvested_by',
        'supervisor_id',
        'supervisor_approved_at',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'harvest_time' => 'datetime',
        'wet_weight' => 'decimal:2',
        'trim_weight' => 'decimal:2',
        'waste_weight' => 'decimal:2',
        'dry_weight' => 'decimal:2',
        'harvested_plant_count' => 'integer',
        'expected_yield' => 'decimal:2',
        'actual_yield' => 'decimal:2',
        'yield_percentage' => 'decimal:2',
        'low_yield_deviation_raised' => 'boolean',
        'lots_created' => 'boolean',
        'supervisor_approved_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function harvestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'harvested_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function calculateYieldPercentage(): ?float
    {
        if (!$this->expected_yield || $this->expected_yield == 0) {
            return null;
        }

        $actual = $this->dry_weight ?? $this->actual_yield ?? 0;
        return ($actual / $this->expected_yield) * 100;
    }

    public function isLowYield(): bool
    {
        $yieldPct = $this->yield_percentage ?? $this->calculateYieldPercentage();
        return $yieldPct !== null && $yieldPct < 85;
    }
}


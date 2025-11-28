<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Strain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'type',
        'genetics',
        'description',
        'thc_min',
        'thc_max',
        'cbd_min',
        'cbd_max',
        'expected_yield_per_plant',
        'expected_flowering_days',
        'expected_vegetative_days',
        'growth_notes',
        'nutrient_requirements',
        'is_active',
    ];

    protected $casts = [
        'thc_min' => 'decimal:2',
        'thc_max' => 'decimal:2',
        'cbd_min' => 'decimal:2',
        'cbd_max' => 'decimal:2',
        'expected_yield_per_plant' => 'decimal:2',
        'expected_flowering_days' => 'integer',
        'expected_vegetative_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->code ? " ({$this->code})" : '');
    }
}


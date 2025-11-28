<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowthCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'facility_id',
        'primary_strain_id',
        'name',
        'status',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $cycle): void {
            if ($cycle->primary_strain_id) {
                $cycle->strains()->syncWithoutDetaching([$cycle->primary_strain_id]);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function primaryStrain(): BelongsTo
    {
        return $this->belongsTo(Strain::class, 'primary_strain_id');
    }

    public function strains(): BelongsToMany
    {
        return $this->belongsToMany(Strain::class)->withTimestamps();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getStrainNamesAttribute(): string
    {
        $names = $this->strains->pluck('name')->filter()->unique()->values();

        if ($names->isEmpty()) {
            return '—';
        }

        return $names->join(', ');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnvironmentalReading extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id',
        'space_type',
        'space_id',
        'temperature',
        'humidity',
        'co2',
        'ph',
        'ec',
        'recorded_at',
        'metadata',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'co2' => 'decimal:2',
        'ph' => 'decimal:2',
        'ec' => 'decimal:2',
        'recorded_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Polymorphic relationship to the monitored space (room or tunnel).
     */
    public function space(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'space_type', 'space_id');
    }
}

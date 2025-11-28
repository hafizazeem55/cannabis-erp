<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tunnel extends Model
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
}

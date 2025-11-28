<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tunnel;

class BatchLog extends Model
{
    protected $fillable = [
        'batch_id',
        'stage',
        'room_id',
        'tunnel_id',
        'log_date',
        'activities',
        'notes',
        'temperature_avg',
        'temperature_min',
        'temperature_max',
        'humidity_avg',
        'humidity_min',
        'humidity_max',
        'co2_avg',
        'ph_avg',
        'ec_avg',
        'plant_count',
        'mortality_count',
        'logged_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'activities' => 'array',
        'temperature_avg' => 'decimal:2',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'humidity_avg' => 'decimal:2',
        'humidity_min' => 'decimal:2',
        'humidity_max' => 'decimal:2',
        'co2_avg' => 'decimal:2',
        'ph_avg' => 'decimal:2',
        'ec_avg' => 'decimal:2',
        'plant_count' => 'integer',
        'mortality_count' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tunnel(): BelongsTo
    {
        return $this->belongsTo(Tunnel::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}


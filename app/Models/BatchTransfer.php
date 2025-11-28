<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchTransfer extends Model
{
    protected $fillable = [
        'batch_id',
        'from_room_id',
        'to_room_id',
        'transfer_date',
        'transfer_time',
        'plant_count',
        'reason',
        'notes',
        'is_planned',
        'triggered_deviation',
        'transferred_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'transfer_time' => 'datetime',
        'plant_count' => 'integer',
        'is_planned' => 'boolean',
        'triggered_deviation' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}


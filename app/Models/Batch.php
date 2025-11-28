<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Batch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'growth_cycle_id',
        'batch_code',
        'strain_id',
        'room_id',
        'parent_batch_id',
        'status',
        'initial_plant_count',
        'current_plant_count',
        'mortality_count',
        'planting_date',
        'clone_date',
        'veg_start_date',
        'flower_start_date',
        'harvest_date',
        'expected_harvest_date',
        'progress_percentage',
        'expected_yield',
        'actual_yield',
        'yield_percentage',
        'notes',
        'metadata',
        'created_by',
        'supervisor_id',
        'is_active',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'clone_date' => 'date',
        'veg_start_date' => 'date',
        'flower_start_date' => 'date',
        'harvest_date' => 'date',
        'expected_harvest_date' => 'date',
        'initial_plant_count' => 'integer',
        'current_plant_count' => 'integer',
        'mortality_count' => 'integer',
        'progress_percentage' => 'decimal:2',
        'expected_yield' => 'decimal:2',
        'actual_yield' => 'decimal:2',
        'yield_percentage' => 'decimal:2',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (empty($batch->batch_code)) {
                $batch->batch_code = static::generateBatchCode();
            }
        });
    }

    public static function generateBatchCode(): string
    {
        $year = date('Y');
        $lastBatch = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $increment = $lastBatch ? (int) Str::afterLast($lastBatch->batch_code, '-') + 1 : 1;
        
        return "B-{$year}-" . str_pad($increment, 4, '0', STR_PAD_LEFT);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function growthCycle(): BelongsTo
    {
        return $this->belongsTo(GrowthCycle::class);
    }

    public function strain(): BelongsTo
    {
        return $this->belongsTo(Strain::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function parentBatch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'parent_batch_id');
    }

    public function childBatches(): HasMany
    {
        return $this->hasMany(Batch::class, 'parent_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function batchLogs(): HasMany
    {
        return $this->hasMany(BatchLog::class)->orderBy('log_date', 'desc');
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(BatchStageHistory::class)->orderBy('transition_date', 'desc');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(BatchTransfer::class)->orderBy('transfer_date', 'desc');
    }

    public function harvest(): HasOne
    {
        return $this->hasOne(Harvest::class);
    }

    public function getSurvivalPercentageAttribute(): float
    {
        if ($this->initial_plant_count == 0) {
            return 0;
        }
        return (($this->current_plant_count / $this->initial_plant_count) * 100);
    }

    public function getMortalityPercentageAttribute(): float
    {
        if ($this->initial_plant_count == 0) {
            return 0;
        }
        return (($this->mortality_count / $this->initial_plant_count) * 100);
    }

    public const STAGE_FLOW = [
        'clone' => [
            'label' => 'Cloning',
        ],
        'propagation' => [
            'label' => 'Propagation',
        ],
        'vegetative' => [
            'label' => 'Vegetative',
        ],
        'flower' => [
            'label' => 'Flower',
        ],
        'harvest' => [
            'label' => 'Harvest',
        ],
        'packaging' => [
            'label' => 'Packaging',
        ],
        'completed' => [
            'label' => 'Completed',
        ],
    ];

    public function canProgressTo(string $newStage): bool
    {
        $validProgressions = [
            'clone' => ['propagation', 'vegetative'],
            'propagation' => ['vegetative'],
            'vegetative' => ['flower'],
            'flower' => ['harvest', 'packaging', 'completed'],
            'harvest' => ['packaging', 'completed'],
            'packaging' => ['completed'],
        ];

        return in_array($newStage, $validProgressions[$this->status] ?? []);
    }

    public function stageProgressionSteps(): array
    {
        $currentStatus = $this->status ?? 'clone';
        if (! array_key_exists($currentStatus, self::STAGE_FLOW)) {
            $currentStatus = 'completed';
        }
        $history = $this->stageHistory()
            ->orderBy('transition_date')
            ->get();

        $stages = [];
        $reachedCurrent = false;

        foreach (self::STAGE_FLOW as $key => $config) {
            if ($key === $currentStatus) {
                $stages[$key] = [
                    'key' => $key,
                    'label' => $config['label'],
                    'state' => 'current',
                    'date' => $history
                        ->firstWhere('to_stage', $key)?->transition_date ?? null,
                ];
                $reachedCurrent = true;
                continue;
            }

            $transitionDate = $history
                ->firstWhere('to_stage', $key)?->transition_date ?? null;

            $stages[$key] = [
                'key' => $key,
                'label' => $config['label'],
                'state' => $reachedCurrent ? 'upcoming' : 'completed',
                'date' => $transitionDate,
            ];

            if ($key === $currentStatus) {
                $reachedCurrent = true;
            }

            if (!$reachedCurrent && $transitionDate === null) {
                $stages[$key]['state'] = 'upcoming';
                $reachedCurrent = true;
            }
        }

        // Ensure stages prior to current are marked as completed
        $foundCurrent = false;
        foreach ($stages as $key => &$stage) {
            if ($stage['state'] === 'current') {
                $foundCurrent = true;
                continue;
            }

            if (! $foundCurrent) {
                $stage['state'] = 'completed';
            }
        }

        if (isset($stages['completed']) && $this->status === 'completed') {
            $stages['completed']['state'] = 'completed';
        }

        return $stages;
    }
}


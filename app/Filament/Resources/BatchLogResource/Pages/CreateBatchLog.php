<?php

namespace App\Filament\Resources\BatchLogResource\Pages;

use App\Filament\Resources\BatchLogResource;
use App\Models\AuditLog;
use App\Models\Batch;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateBatchLog extends CreateRecord
{
    protected static string $resource = BatchLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set logged_by
        $data['logged_by'] = auth()->id();

        // Auto-populate room from batch if not set
        if (!isset($data['room_id']) && isset($data['batch_id'])) {
            $batch = Batch::find($data['batch_id']);
            if ($batch && $batch->room_id) {
                $data['room_id'] = $batch->room_id;
            }
            if (empty($data['stage'])) {
                $data['stage'] = BatchLogResource::normalizeStage($batch?->status);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $batchLog = $this->record;
        $batch = $batchLog->batch;
        $normalizedStatus = BatchLogResource::normalizeStage($batch->status);

        // Update batch plant counts if provided
        if ($batchLog->plant_count !== null) {
            $batch->current_plant_count = $batchLog->plant_count;
        }

        // Update mortality count
        if ($batchLog->mortality_count > 0) {
            $batch->mortality_count += $batchLog->mortality_count;
        }

        // Recalculate progress percentage based on logs
        $totalLogs = $batch->batchLogs()->count();
        $stageDurations = [
            'cloning' => 14,
            'clone' => 14,
            'propagation' => 14,
            'vegetative' => $batch->strain->expected_vegetative_days ?? 30,
            'flowering' => $batch->strain->expected_flowering_days ?? 60,
            'flower' => $batch->strain->expected_flowering_days ?? 60,
            'harvest' => 7,
            'drying' => 10,
            'curing' => 14,
            'packaging' => 7,
        ];
        $expectedDays = $stageDurations[$normalizedStatus] ?? 0;

        if ($expectedDays > 0) {
            $progress = min(100, ($totalLogs / $expectedDays) * 100);
            $batch->progress_percentage = $progress;
        }

        $batch->save();

        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => static::getModel(),
            'model_id' => $batchLog->id,
            'changes' => [
                'before' => null,
                'after' => $batchLog->toArray(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

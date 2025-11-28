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
                $data['stage'] = $batch?->status;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $batchLog = $this->record;
        $batch = $batchLog->batch;

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
        $expectedDays = 0;
        
        // Calculate expected days based on current stage
        switch ($batch->status) {
            case 'clone':
            case 'propagation':
                $expectedDays = 14; // Example: 2 weeks for propagation
                break;
            case 'vegetative':
                $expectedDays = $batch->strain->expected_vegetative_days ?? 30;
                break;
            case 'flower':
                $expectedDays = $batch->strain->expected_flowering_days ?? 60;
                break;
        }

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

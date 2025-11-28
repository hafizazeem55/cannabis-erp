<?php

namespace App\Filament\Resources\BatchLogResource\Pages;

use App\Filament\Resources\BatchLogResource;
use App\Models\AuditLog;
use App\Models\Batch;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBatchLog extends EditRecord
{
    protected static string $resource = BatchLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    protected function beforeSave(): void
    {
        $this->originalData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        $batchLog = $this->record;
        $batch = $batchLog->batch;

        // Update batch plant counts if changed
        if ($batchLog->wasChanged('plant_count') && $batchLog->plant_count !== null) {
            $batch->current_plant_count = $batchLog->plant_count;
        }

        // Recalculate mortality if changed
        if ($batchLog->wasChanged('mortality_count')) {
            $oldMortality = $this->originalData['mortality_count'] ?? 0;
            $newMortality = $batchLog->mortality_count ?? 0;
            $difference = $newMortality - $oldMortality;
            $batch->mortality_count = max(0, $batch->mortality_count + $difference);
        }

        // Recalculate progress
        $totalLogs = $batch->batchLogs()->count();
        $expectedDays = 0;
        
        switch ($batch->status) {
            case 'clone':
            case 'propagation':
                $expectedDays = 14;
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

        // Log the update
        $changes = [];
        foreach ($batchLog->getDirty() as $key => $value) {
            $changes[$key] = [
                'before' => $this->originalData[$key] ?? null,
                'after' => $value,
            ];
        }

        if (!empty($changes)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'model_type' => static::getModel(),
                'model_id' => $batchLog->id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}

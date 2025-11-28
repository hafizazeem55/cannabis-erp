<?php

namespace App\Filament\Resources\HarvestResource\Pages;

use App\Filament\Resources\HarvestResource;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Harvest;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateHarvest extends CreateRecord
{
    protected static string $resource = HarvestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set harvested_by
        $data['harvested_by'] = auth()->id();

        // Calculate yield percentage if expected and actual yields are set
        if (isset($data['expected_yield']) && isset($data['dry_weight']) && $data['expected_yield'] > 0) {
            $data['actual_yield'] = $data['dry_weight'];
            $data['yield_percentage'] = ($data['dry_weight'] / $data['expected_yield']) * 100;
        } elseif (isset($data['expected_yield']) && isset($data['wet_weight']) && $data['expected_yield'] > 0) {
            // Use wet weight if dry weight not available
            $data['actual_yield'] = $data['wet_weight'];
            $data['yield_percentage'] = ($data['wet_weight'] / $data['expected_yield']) * 100;
        }

        // Check for low yield
        if (isset($data['yield_percentage']) && $data['yield_percentage'] < 85) {
            $data['low_yield_deviation_raised'] = false; // Will be raised after creation
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $harvest = $this->record;
        $batch = $harvest->batch;

        // Update batch with harvest data
        $batch->update([
            'harvest_date' => $harvest->harvest_date,
            'actual_yield' => $harvest->dry_weight ?? $harvest->wet_weight,
            'yield_percentage' => $harvest->yield_percentage,
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);

        // Check for low yield and raise deviation if needed
        if ($harvest->isLowYield() && !$harvest->low_yield_deviation_raised) {
            // TODO: Create deviation record when QMS module is ready
            // For now, just mark the flag
            $harvest->update(['low_yield_deviation_raised' => true]);
            
            // You can add notification here
            \Filament\Notifications\Notification::make()
                ->title('Low Yield Detected')
                ->body("Harvest yield is below 85%. Deviation should be raised.")
                ->warning()
                ->send();
        }

        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => static::getModel(),
            'model_id' => $harvest->id,
            'changes' => [
                'before' => null,
                'after' => $harvest->toArray(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

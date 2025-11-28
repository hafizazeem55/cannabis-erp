<?php

namespace App\Filament\Resources\HarvestResource\Pages;

use App\Filament\Resources\HarvestResource;
use App\Models\AuditLog;
use App\Models\Batch;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHarvest extends EditRecord
{
    protected static string $resource = HarvestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate yield percentage if dry weight or expected yield changed
        if (isset($data['dry_weight']) && isset($data['expected_yield']) && $data['expected_yield'] > 0) {
            $data['actual_yield'] = $data['dry_weight'];
            $data['yield_percentage'] = ($data['dry_weight'] / $data['expected_yield']) * 100;
        } elseif (isset($data['wet_weight']) && isset($data['expected_yield']) && $data['expected_yield'] > 0 && !isset($data['dry_weight'])) {
            $data['actual_yield'] = $data['wet_weight'];
            $data['yield_percentage'] = ($data['wet_weight'] / $data['expected_yield']) * 100;
        }

        // Check for low yield
        if (isset($data['yield_percentage']) && $data['yield_percentage'] < 85) {
            if (!$this->record->low_yield_deviation_raised) {
                // TODO: Create deviation when QMS module is ready
                $data['low_yield_deviation_raised'] = true;
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->originalData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        $harvest = $this->record;
        $batch = $harvest->batch;

        // Update batch with latest harvest data
        $batch->update([
            'actual_yield' => $harvest->dry_weight ?? $harvest->wet_weight,
            'yield_percentage' => $harvest->yield_percentage,
        ]);

        // Log the update
        $changes = [];
        foreach ($harvest->getDirty() as $key => $value) {
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
                'model_id' => $harvest->id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}

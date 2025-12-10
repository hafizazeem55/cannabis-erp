<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchStageHistory;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('progress_stage')
                ->label('Progress Stage')
                ->icon('heroicon-o-arrow-right')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('new_stage')
                        ->label('New Stage')
                        ->required()
                        ->options(collect(Batch::STAGE_FLOW)
                            ->except('cloning')
                            ->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
                            ->toArray())
                        ->native(false),
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $batch = $this->record;
                    $newStage = $data['new_stage'];
                    $reason = $data['reason'] ?? '';

                    if (!$batch->canProgressTo($newStage)) {
                        Notification::make()
                            ->title('Invalid Stage Progression')
                            ->body("Cannot progress from {$this->normalizeStage($batch->status)} to {$newStage}")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Check if user has permission (supervisor or admin)
                    $user = auth()->user();
                    if (!$user->hasRole(['Administrator', 'Cultivation Supervisor']) && !$user->can('approve cultivation')) {
                        Notification::make()
                            ->title('Permission Denied')
                            ->body('Only supervisors can progress batch stages')
                            ->danger()
                            ->send();
                        return;
                    }

                    $oldStage = $batch->status;
                    $normalizedOldStage = $this->normalizeStage($oldStage);
                    $normalizedNewStage = $this->normalizeStage($newStage);

                    // Record stage history
                    BatchStageHistory::create([
                        'batch_id' => $batch->id,
                        'from_stage' => $normalizedOldStage,
                        'to_stage' => $normalizedNewStage,
                        'transition_date' => now(),
                        'reason' => $reason,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    // Update batch status and dates
                    $updateData = ['status' => $normalizedNewStage];
                    
                    switch ($normalizedNewStage) {
                        case 'cloning':
                            $updateData['clone_date'] = now();
                            break;
                        case 'vegetative':
                            $updateData['veg_start_date'] = now();
                            break;
                        case 'flowering':
                            $updateData['flower_start_date'] = now();
                            break;
                        case 'harvest':
                            $updateData['harvest_date'] = now();
                            break;
                    }

                    $batch->update($updateData);

                    // Log the change
                    AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'updated',
                        'model_type' => Batch::class,
                        'model_id' => $batch->id,
                        'changes' => [
                            'status' => [
                                'before' => $normalizedOldStage,
                                'after' => $normalizedNewStage,
                            ],
                        ],
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    Notification::make()
                        ->title('Stage Progressed')
                        ->body("Batch {$batch->batch_code} progressed from {$normalizedOldStage} to {$normalizedNewStage}")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'veg_start_date', 'flower_start_date', 'harvest_date']);
                })
                ->visible(fn () => 
                    $this->record && 
                    !in_array($this->record->status, ['completed', 'cancelled']) &&
                    (auth()->user()?->hasRole(['Administrator', 'Cultivation Supervisor']) || auth()->user()?->can('approve cultivation'))
                ),

            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate room capacity if room or plant count changed
        if (isset($data['room_id']) && isset($data['current_plant_count'])) {
            $room = Room::find($data['room_id']);
            if ($room) {
                $currentUtilization = $room->current_utilization;
                $thisBatchCount = $this->record->current_plant_count ?? 0;
                $availableCapacity = $room->capacity - ($currentUtilization - $thisBatchCount);
                
                if ($data['current_plant_count'] > $availableCapacity) {
                    throw new \Exception("Room capacity exceeded. Available capacity: {$availableCapacity}");
                }
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
        $changes = [];
        foreach ($this->record->getDirty() as $key => $value) {
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
                'model_id' => $this->record->id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    protected function normalizeStage(?string $stage): string
    {
        return match ($stage) {
            'clone', 'propagation' => 'cloning',
            'flower' => 'flowering',
            default => $stage ?? 'cloning',
        };
    }
}

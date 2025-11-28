<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchLogResource;
use App\Filament\Resources\BatchResource;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchStageHistory;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    // protected ?string $maxContentWidth = MaxWidth::Full->value;

    // public function getMaxContentWidth(): MaxWidth | string | null
    // {
    //     return MaxWidth::Full;
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_stage')
                ->label('Manage Stage')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('new_stage')
                        ->label('New Stage')
                        ->required()
                        ->options(collect(Batch::STAGE_FLOW)
                            ->except('clone')
                            ->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
                            ->toArray()
                        )
                        ->native(false),
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    /** @var Batch $batch */
                    $batch = $this->record;
                    $newStage = $data['new_stage'];
                    $reason = $data['reason'] ?? '';

                    if (! $batch->canProgressTo($newStage)) {
                        Notification::make()
                            ->title('Invalid Stage Progression')
                            ->body("Cannot progress from {$batch->status} to {$newStage}.")
                            ->danger()
                            ->send();

                        return;
                    }

                    $user = auth()->user();
                    if (! $user->hasRole(['Administrator', 'Cultivation Supervisor']) && ! $user->can('approve cultivation')) {
                        Notification::make()
                            ->title('Permission Denied')
                            ->body('Only supervisors can progress batch stages.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $oldStage = $batch->status;

                    BatchStageHistory::create([
                        'batch_id' => $batch->id,
                        'from_stage' => $oldStage,
                        'to_stage' => $newStage,
                        'transition_date' => now(),
                        'reason' => $reason,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    $updateData = ['status' => $newStage];

                    switch ($newStage) {
                        case 'vegetative':
                            $updateData['veg_start_date'] = now();
                            break;
                        case 'flower':
                            $updateData['flower_start_date'] = now();
                            break;
                        case 'harvest':
                            $updateData['harvest_date'] = now();
                            break;
                    }

                    $batch->update($updateData);

                    AuditLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'updated',
                        'model_type' => Batch::class,
                        'model_id' => $batch->id,
                        'changes' => [
                            'status' => [
                                'before' => $oldStage,
                                'after' => $newStage,
                            ],
                        ],
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    Notification::make()
                        ->title('Stage Progressed')
                        ->body("Batch {$batch->batch_code} progressed from {$oldStage} to {$newStage}.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool =>
                    $this->record &&
                    ! in_array($this->record->status, ['completed', 'cancelled']) &&
                    (auth()->user()?->hasRole(['Administrator', 'Cultivation Supervisor']) || auth()->user()?->can('approve cultivation'))
                ),

            Actions\Action::make('add_log')
                ->label('Add Daily Log')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->url(fn (): string => BatchLogResource::getUrl('create', ['batch_id' => $this->record->id])),

            Actions\EditAction::make(),
        ];
    }
}


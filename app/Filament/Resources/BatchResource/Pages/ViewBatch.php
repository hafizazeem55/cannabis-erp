<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchLogResource;
use App\Filament\Resources\BatchResource;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchStageHistory;
use App\Models\BatchLog;
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

    public function startStage(string $stageKey): void
    {
        /** @var Batch $batch */
        $batch = $this->record->fresh();
        $oldStage = $batch->status;

        if (! $this->canManageStages()) {
            $this->notifyDenied();
            return;
        }

        $next = $this->nextStageKey($batch->status);
        if (! $next || $next !== $stageKey) {
            Notification::make()
                ->title('Cannot start this stage')
                ->body('Only the next pending stage can be started.')
                ->danger()
                ->send();
            return;
        }

        if (! $batch->canProgressTo($stageKey)) {
            Notification::make()
                ->title('Invalid stage order')
                ->body("Cannot progress from {$batch->status} to {$stageKey}.")
                ->danger()
                ->send();
            return;
        }

        $this->createHistory($batch->status, $stageKey);
        $batch->update($this->stageUpdatePayload($stageKey));

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => Batch::class,
            'model_id' => $batch->id,
            'changes' => [
                'status' => [
                    'before' => $oldStage,
                    'after' => $stageKey,
                ],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Notification::make()->title('Stage started')->body("Stage set to {$stageKey}.")->success()->send();
        $this->refreshRecord();
    }

    public function completeStage(string $stageKey): void
    {
        /** @var Batch $batch */
        $batch = $this->record->fresh();
        $oldStage = $batch->status;

        if (! $this->canManageStages()) {
            $this->notifyDenied();
            return;
        }

        if ($batch->status !== $stageKey) {
            Notification::make()
                ->title('Cannot complete')
                ->body('Only the active stage can be completed.')
                ->danger()
                ->send();
            return;
        }

        $next = $this->nextStageKey($stageKey);
        if (! $next) {
            Notification::make()
                ->title('No next stage found')
                ->body('This batch is already at the final stage.')
                ->danger()
                ->send();
            return;
        }

        if (! $batch->canProgressTo($next)) {
            Notification::make()
                ->title('Invalid stage order')
                ->body("Cannot progress from {$batch->status} to {$next}.")
                ->danger()
                ->send();
            return;
        }

        $logCount = BatchLog::where('batch_id', $batch->id)
            ->where('stage', $stageKey)
            ->count();

        if ($logCount < 1) {
            Notification::make()
                ->title('Add a daily log first')
                ->body('Please add at least one daily log before completing this stage.')
                ->warning()
                ->send();
            return;
        }

        $this->createHistory($stageKey, $next);
        $batch->update($this->stageUpdatePayload($next));

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => Batch::class,
            'model_id' => $batch->id,
            'changes' => [
                'status' => [
                    'before' => $oldStage,
                    'after' => $next,
                ],
            ],
            'notes' => 'Completed stage via Stage Progression tab',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Notification::make()->title('Stage completed')->body("Moved from {$stageKey} to {$next}.")->success()->send();
        $this->refreshRecord();
    }

    protected function refreshRecord(): void
    {
        $this->record->refresh();
        $this->dispatch('$refresh');
    }

    protected function canManageStages(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole(['Administrator', 'Cultivation Supervisor']) || $user->can('approve cultivation'));
    }

    protected function notifyDenied(): void
    {
        Notification::make()
            ->title('Permission denied')
            ->body('Only supervisors or approvers can change stages.')
            ->danger()
            ->send();
    }

    protected function nextStageKey(string $current): ?string
    {
        $keys = array_keys(Batch::STAGE_FLOW);
        $index = array_search($current, $keys, true);
        return $index === false ? null : ($keys[$index + 1] ?? null);
    }

    protected function stageUpdatePayload(string $newStage): array
    {
        $payload = ['status' => $newStage];

        switch ($newStage) {
            case 'clone':
                $payload['clone_date'] = now();
                break;
            case 'vegetative':
                $payload['veg_start_date'] = now();
                break;
            case 'flower':
                $payload['flower_start_date'] = now();
                break;
            case 'harvest':
                $payload['harvest_date'] = now();
                break;
        }

        return $payload;
    }

    protected function createHistory(?string $from, string $to): void
    {
        BatchStageHistory::create([
            'batch_id' => $this->record->id,
            'from_stage' => $from,
            'to_stage' => $to,
            'transition_date' => now(),
            'reason' => null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'created_by' => auth()->id(),
        ]);
    }
}


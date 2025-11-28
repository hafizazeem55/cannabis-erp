<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Strain;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction as TablesViewAction;
use Illuminate\Database\Eloquent\Builder;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Batches';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Batch Information')
                ->schema([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->relationship('organization', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn () => auth()->user()?->hasRole('Administrator'))
                        ->reactive(),

                    Select::make('growth_cycle_id')
                        ->label('Growth Cycle')
                        ->relationship('growthCycle', 'name', function (Builder $query) {
                            if (! auth()->user()?->hasRole('Administrator')) {
                                $query->where('organization_id', auth()->user()?->organization_id);
                            }

                            return $query;
                        })
                        ->searchable()
                        ->preload()
                        ->default(fn () => request()->has('growth_cycle_id') ? request()->integer('growth_cycle_id') : null)
                        ->helperText('Optionally link this batch to a cultivation cycle'),

                    Placeholder::make('batch_code')
                        ->label('Batch Code')
                        ->content(fn ($record) => $record?->batch_code ?? 'Auto-generated on save')
                        ->visible(fn ($record) => $record !== null),

                    Select::make('strain_id')
                        ->label('Strain')
                        ->relationship('strain', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $strain = Strain::find($state);
                                if ($strain && $strain->expected_yield_per_plant) {
                                    // Auto-calculate expected yield based on initial plant count
                                    // This will be handled in the create/edit pages
                                }
                            }
                        }),

                    Select::make('room_id')
                        ->label('Room')
                        ->relationship('room', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            if ($state) {
                                $room = Room::find($state);
                                if ($room) {
                                    $currentUtilization = $room->current_utilization;
                                    $availableCapacity = $room->available_capacity;
                                    $initialCount = (int) $get('initial_plant_count');
                                    
                                    if ($initialCount > $availableCapacity) {
                                        $set('initial_plant_count', $availableCapacity);
                                    }
                                }
                            }
                        }),

                    Select::make('parent_batch_id')
                        ->label('Parent Batch (if split)')
                        ->relationship('parentBatch', 'batch_code')
                        ->searchable()
                        ->preload()
                        ->helperText('Select if this batch is split from another batch'),

                    Select::make('status')
                        ->label('Status')
                        ->required()
                        ->options(static::statusOptions())
                        ->default('clone')
                        ->native(false)
                        ->disabled(fn ($record) => $record && in_array($record->status, ['completed', 'cancelled'])),
                ])
                ->columns(2),

            Section::make('Plant Counts')
                ->schema([
                    TextInput::make('initial_plant_count')
                        ->label('Initial Plant Count')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            // Validate against room capacity
                            $roomId = $get('room_id');
                            if ($roomId && $state) {
                                $room = Room::find($roomId);
                                if ($room) {
                                    $availableCapacity = $room->available_capacity;
                                    if ($state > $availableCapacity) {
                                        $set('initial_plant_count', $availableCapacity);
                                    }
                                }
                            }
                            // Set current plant count to initial if not set
                            if (!$get('current_plant_count')) {
                                $set('current_plant_count', $state);
                            }
                        }),

                    TextInput::make('current_plant_count')
                        ->label('Current Plant Count')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Updated automatically from daily logs'),

                    TextInput::make('mortality_count')
                        ->label('Mortality Count')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Number of plants that died'),

                    Placeholder::make('survival_percentage')
                        ->label('Survival %')
                        ->content(fn ($record) => $record 
                            ? number_format($record->survival_percentage, 1) . '%'
                            : 'N/A')
                        ->visible(fn ($record) => $record !== null),
                ])
                ->columns(2),

            Section::make('Dates')
                ->schema([
                    DatePicker::make('planting_date')
                        ->label('Planting Date')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),

                    DatePicker::make('clone_date')
                        ->label('Clone Date')
                        ->maxDate(now()),

                    DatePicker::make('veg_start_date')
                        ->label('Vegetative Start Date')
                        ->maxDate(now()),

                    DatePicker::make('flower_start_date')
                        ->label('Flower Start Date')
                        ->maxDate(now()),

                    DatePicker::make('expected_harvest_date')
                        ->label('Expected Harvest Date')
                        ->helperText('Estimated harvest date based on strain characteristics'),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),

            Section::make('Yield Tracking')
                ->schema([
                    TextInput::make('expected_yield')
                        ->label('Expected Yield (grams)')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Total expected yield for this batch'),

                    Placeholder::make('actual_yield')
                        ->label('Actual Yield (grams)')
                        ->content(fn ($record) => $record && $record->actual_yield 
                            ? number_format($record->actual_yield, 2) . 'g'
                            : 'N/A')
                        ->visible(fn ($record) => $record !== null),

                    Placeholder::make('yield_percentage')
                        ->label('Yield %')
                        ->content(fn ($record) => $record && $record->yield_percentage 
                            ? number_format($record->yield_percentage, 1) . '%'
                            : 'N/A')
                        ->visible(fn ($record) => $record !== null),

                    Placeholder::make('progress_percentage')
                        ->label('Progress %')
                        ->content(fn ($record) => $record 
                            ? number_format($record->progress_percentage, 1) . '%'
                            : '0%')
                        ->visible(fn ($record) => $record !== null),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->visible(fn ($record) => $record !== null),

            Section::make('Additional Information')
                ->schema([
                    Select::make('supervisor_id')
                        ->label('Supervisor')
                        ->relationship('supervisor', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Supervisor responsible for this batch'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive batches are hidden from active views'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Batch $record): string => static::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('batch_code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->label('Batch Code'),

                TextColumn::make('strain.name')
                    ->label('Strain')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('growthCycle.name')
                    ->label('Growth Cycle')
                    ->badge()
                    ->color('primary')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('room.name')
                    ->label('Room')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (string $state): string => static::statusLabel($state)),

                TextColumn::make('current_plant_count')
                    ->label('Plants')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('survival_percentage')
                    ->label('Survival %')
                    ->getStateUsing(fn (Batch $record) => number_format($record->survival_percentage, 1) . '%')
                    ->badge()
                    ->color(fn (Batch $record) => $record->survival_percentage > 90 ? 'success' : ($record->survival_percentage > 75 ? 'warning' : 'danger'))
                    ->sortable(),

                TextColumn::make('progress_percentage')
                    ->label('Progress %')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) . '%' : '0%')
                    ->badge()
                    ->color(fn (?string $state) => (float) $state > 90 ? 'success' : ((float) $state > 50 ? 'info' : 'gray'))
                    ->sortable(),

                TextColumn::make('expected_harvest_date')
                    ->label('Expected Harvest')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->expected_harvest_date && $record->expected_harvest_date->isPast() ? 'danger' : null),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions()),

                SelectFilter::make('strain_id')
                    ->label('Strain')
                    ->relationship('strain', 'name')
                    ->searchable(),

                SelectFilter::make('growth_cycle_id')
                    ->label('Growth Cycle')
                    ->relationship('growthCycle', 'name')
                    ->searchable(),

                SelectFilter::make('room_id')
                    ->label('Room')
                    ->relationship('room', 'name')
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All batches')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                TablesViewAction::make()
                    ->url(fn (Batch $record) => static::getUrl('view', ['record' => $record])),

                Action::make('view_logs')
                    ->label('View Logs')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (Batch $record) => BatchLogResource::getUrl('index', ['batch_id' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('view_stage_history')
                    ->label('Stage History')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modalHeading(fn (Batch $record) => "Stage History - {$record->batch_code}")
                    ->modalContent(fn (Batch $record) => view('filament.resources.batch-resource.stage-history', [
                        'stages' => $record->stageHistory()->with('approvedBy', 'createdBy')->get(),
                        'logCounts' => static::logCountsByStage($record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                Tabs::make('Batch Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->extraAttributes([
                        'class' => 'gap-6',
                    ])
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                ViewEntry::make('overview')
                                    ->label('')
                                    ->view('filament.resources.batch-resource.overview')
                                    ->viewData(fn (Batch $record) => [
                                        'record' => $record,
                                        'statusLabel' => static::statusLabel($record->status),
                                        'backUrl' => static::getUrl('index'),
                                        'editUrl' => static::getUrl('edit', ['record' => $record]),
                                        'canManageStage' => auth()->user()?->hasRole(['Administrator', 'Cultivation Supervisor'])
                                            || auth()->user()?->can('approve cultivation'),
                                    ]),
                            ]),
                        Tab::make('Stage Progression')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                ViewEntry::make('stage_progression')
                                    ->label('')
                                    ->view('filament.resources.batch-resource.stage-progression')
                                    ->viewData(fn (Batch $record) => [
                                        'stages' => $record->stageProgressionSteps(),
                                        'record' => $record,
                                        'history' => $record->stageHistory()->with('approvedBy')->orderBy('transition_date')->get(),
                                        'logCounts' => static::logCountsByStage($record),
                                        'logsByStage' => static::logsByStage($record),
                                    ]),
                            ]),
                        Tab::make('Daily Logs')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                ViewEntry::make('daily_logs')
                                    ->label('')
                                    ->view('filament.resources.batch-resource.daily-logs')
                                    ->viewData(fn (Batch $record) => [
                                        'logs' => $record->batchLogs()->limit(10)->get(),
                                        'record' => $record,
                                    ]),
                            ]),
                        Tab::make('Deviations')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                ViewEntry::make('deviations')
                                    ->label('')
                                    ->view('filament.resources.batch-resource.deviations')
                                    ->viewData(fn (Batch $record) => [
                                        'record' => $record,
                                    ]),
                            ]),
                        Tab::make('Photos')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                ViewEntry::make('photos')
                                    ->label('')
                                    ->view('filament.resources.batch-resource.photos')
                                    ->viewData(fn (Batch $record) => [
                                        'record' => $record,
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected static function statusOptions(): array
    {
        return collect(Batch::STAGE_FLOW)
            ->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
            ->put('cancelled', 'Cancelled')
            ->toArray();
    }

    protected static function statusLabel(string $state): string
    {
        return static::statusOptions()[$state] ?? ucfirst($state);
    }

    protected static function statusColor(string $state): string
    {
        return match ($state) {
            'clone', 'propagation' => 'gray',
            'vegetative' => 'info',
            'flower' => 'warning',
            'harvest', 'packaging', 'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    protected static function logsByStage(Batch $batch): array
    {
        return $batch->batchLogs()
            ->with(['room:id,name', 'tunnel:id,name'])
            ->orderByDesc('log_date')
            ->get()
            ->groupBy('stage')
            ->map(function ($logs) {
                return $logs->map(function (BatchLog $log) {
                    return [
                        'id' => $log->id,
                        'stage' => $log->stage,
                        'date' => optional($log->log_date)->format('M d, Y'),
                        'notes' => $log->notes,
                        'activities' => collect($log->activities ?? [])
                            ->map(fn ($activity) => $activity['activity'] ?? null)
                            ->filter()
                            ->values()
                            ->all(),
                        'room' => $log->room?->name,
                        'tunnel' => $log->tunnel?->name,
                        'plant_count' => $log->plant_count,
                        'mortality_count' => $log->mortality_count,
                        'edit_url' => BatchLogResource::getUrl('edit', ['record' => $log]),
                    ];
                })->values()->all();
            })
            ->toArray();
    }

    protected static function logCountsByStage(Batch $batch): array
    {
        return $batch->batchLogs()
            ->selectRaw('stage, COUNT(*) as aggregate')
            ->groupBy('stage')
            ->pluck('aggregate', 'stage')
            ->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'view' => Pages\ViewBatch::route('/{record}'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}


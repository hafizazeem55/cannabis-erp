<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchLogResource\Pages;
use App\Models\BatchLog;
use App\Models\Batch;
use App\Models\Room;
use App\Models\Tunnel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BatchLogResource extends Resource
{
    protected static ?string $model = BatchLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Batch Logs';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Log Information')
                ->schema([
                    Select::make('batch_id')
                        ->label('Batch')
                        ->relationship('batch', 'batch_code', fn (Builder $query) =>
                            $query->where('is_active', true)
                                ->whereNotIn('status', ['completed', 'cancelled'])
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->default(fn (?BatchLog $record) => $record?->batch_id ?? request()->integer('batch_id'))
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $batch = Batch::find($state);
                                if ($batch && $batch->room_id) {
                                    $set('room_id', $batch->room_id);
                                }
                                $set('stage', $batch?->status);
                            } else {
                                $set('stage', null);
                            }
                        }),

                    Select::make('room_id')
                        ->label('Room')
                        ->relationship('room', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload(),

                    Select::make('tunnel_id')
                        ->label('Tunnel')
                        ->relationship('tunnel', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload(),

                    DatePicker::make('log_date')
                        ->label('Log Date')
                        ->required()
                        ->default(now())
                        ->maxDate(now())
                        ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                            return $rule->where('batch_id', $get('batch_id'));
                        })
                        ->helperText('One log per batch per day'),

                    Select::make('stage')
                        ->label('Stage')
                        ->options(static::stageOptions())
                        ->required()
                        ->searchable()
                        ->preload()
                        ->default(function (?BatchLog $record, callable $get) {
                            if ($record?->stage) {
                                return $record->stage;
                            }

                            $batchId = $get('batch_id') ?: request()->integer('batch_id');
                            return static::defaultStageFromBatch($batchId);
                        })
                        ->helperText('Logs are grouped and displayed per stage.'),
                ])
                ->columns(3),

            Section::make('Daily Activities')
                ->description('Record daily activities such as watering, pruning, nutrient application, etc.')
                ->schema([
                    Repeater::make('activities')
                        ->label('Activities')
                        ->schema([
                            TextInput::make('activity')
                                ->label('Activity Type')
                                ->required()
                                ->placeholder('e.g., Watering, Pruning, Nutrient Application'),
                            TextInput::make('details')
                                ->label('Details')
                                ->placeholder('e.g., 5L water, NPK 20-20-20'),
                            TextInput::make('time')
                                ->label('Time')
                                ->placeholder('e.g., 09:00 AM'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['activity'] ?? null),
                ])
                ->collapsible(),

            Section::make('Environmental Data')
                ->description('Record environmental conditions (can be auto-populated from sensors)')
                ->schema([
                    TextInput::make('temperature_avg')
                        ->label('Temperature Avg (°C)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('temperature_min')
                        ->label('Temperature Min (°C)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('temperature_max')
                        ->label('Temperature Max (°C)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('humidity_avg')
                        ->label('Humidity Avg (%)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('humidity_min')
                        ->label('Humidity Min (%)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('humidity_max')
                        ->label('Humidity Max (%)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('co2_avg')
                        ->label('CO₂ Avg (ppm)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ph_avg')
                        ->label('pH Avg')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ec_avg')
                        ->label('EC Avg (mS/cm)')
                        ->numeric()
                        ->step(0.01),
                ])
                ->columns(3)
                ->collapsible()
                ->collapsed(),

            Section::make('Plant Status')
                ->schema([
                    TextInput::make('plant_count')
                        ->label('Plant Count')
                        ->numeric()
                        ->integer()
                        ->helperText('Current plant count for this day'),

                    TextInput::make('mortality_count')
                        ->label('Mortality Count')
                        ->numeric()
                        ->integer()
                        ->default(0)
                        ->helperText('Plants that died today'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn (BatchLog $record) => BatchResource::getUrl('edit', ['record' => $record->batch_id]))
                    ->openUrlInNewTab(),

                TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => static::stageOptions()[$state] ?? ucfirst($state ?? ''))
                    ->sortable(),

                TextColumn::make('log_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('room.name')
                    ->label('Room')
                    ->badge()
                    ->color('info'),

                TextColumn::make('tunnel.name')
                    ->label('Tunnel')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('temperature_avg')
                    ->label('Temp (°C)')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) : 'N/A')
                    ->sortable(),

                TextColumn::make('humidity_avg')
                    ->label('Humidity (%)')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) : 'N/A')
                    ->sortable(),

                TextColumn::make('plant_count')
                    ->label('Plants')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('mortality_count')
                    ->label('Mortality')
                    ->badge()
                    ->color(fn (?int $state): string => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('loggedBy.name')
                    ->label('Logged By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('batch_id')
                    ->label('Batch')
                    ->relationship('batch', 'batch_code')
                    ->searchable(),

                SelectFilter::make('room_id')
                    ->label('Room')
                    ->relationship('room', 'name')
                    ->searchable(),

                SelectFilter::make('tunnel_id')
                    ->label('Tunnel')
                    ->relationship('tunnel', 'name')
                    ->searchable(),

                SelectFilter::make('stage')
                    ->label('Stage')
                    ->options(static::stageOptions()),

                Filter::make('log_date')
                    ->form([
                        DatePicker::make('logged_from')
                            ->label('Logged from'),
                        DatePicker::make('logged_until')
                            ->label('Logged until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['logged_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('log_date', '>=', $date),
                            )
                            ->when(
                                $data['logged_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('log_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
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
            ->defaultSort('log_date', 'desc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatchLogs::route('/'),
            'create' => Pages\CreateBatchLog::route('/create'),
            'edit' => Pages\EditBatchLog::route('/{record}/edit'),
        ];
    }

    protected static function stageOptions(): array
    {
        return collect(Batch::STAGE_FLOW)
            ->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
            ->toArray();
    }

    protected static function defaultStageFromBatch(?int $batchId): ?string
    {
        if (! $batchId) {
            return null;
        }

        return Batch::find($batchId)?->status;
    }
}

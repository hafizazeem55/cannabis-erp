<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HarvestResource\Pages;
use App\Models\Harvest;
use App\Models\Batch;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class HarvestResource extends Resource
{
    protected static ?string $model = Harvest::class;
    protected static ?string $navigationIcon = 'heroicon-o-scissors';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Harvests';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Harvest Information')
                ->schema([
                    Select::make('batch_id')
                        ->label('Batch')
                        ->relationship('batch', 'batch_code', fn (Builder $query) => 
                            $query->where('is_active', true)
                                ->whereIn('status', ['flower', 'harvest'])
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $batch = Batch::find($state);
                                if ($batch) {
                                    $set('room_id', $batch->room_id);
                                    $set('expected_yield', $batch->expected_yield);
                                    $set('harvested_plant_count', $batch->current_plant_count);
                                }
                            }
                        }),

                    Select::make('room_id')
                        ->label('Room')
                        ->relationship('room', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload(),

                    DatePicker::make('harvest_date')
                        ->label('Harvest Date')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),

                    TimePicker::make('harvest_time')
                        ->label('Harvest Time')
                        ->default(now()),
                ])
                ->columns(2),

            Section::make('Weights (in grams)')
                ->description('Record all weights from the harvest')
                ->schema([
                    TextInput::make('wet_weight')
                        ->label('Wet Weight (g)')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->helperText('Total wet weight immediately after harvest'),

                    TextInput::make('trim_weight')
                        ->label('Trim Weight (g)')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->helperText('Trim/leaf material'),

                    TextInput::make('waste_weight')
                        ->label('Waste Weight (g)')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->helperText('Waste material'),

                    TextInput::make('dry_weight')
                        ->label('Dry Weight (g)')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Dry weight after curing (can be updated later)'),

                    TextInput::make('harvested_plant_count')
                        ->label('Harvested Plant Count')
                        ->numeric()
                        ->integer()
                        ->required()
                        ->default(0),
                ])
                ->columns(2),

            Section::make('Yield Calculations')
                ->schema([
                    TextInput::make('expected_yield')
                        ->label('Expected Yield (g)')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Expected yield from batch'),

                    Placeholder::make('actual_yield')
                        ->label('Actual Yield (g)')
                        ->content(fn ($record) => $record && $record->dry_weight 
                            ? number_format($record->dry_weight, 2) . 'g'
                            : ($record && $record->wet_weight 
                                ? number_format($record->wet_weight, 2) . 'g (wet)'
                                : 'N/A'))
                        ->visible(fn ($record) => $record !== null),

                    Placeholder::make('yield_percentage')
                        ->label('Yield %')
                        ->content(fn ($record) => $record && $record->yield_percentage 
                            ? number_format($record->yield_percentage, 1) . '%'
                            : 'N/A')
                        ->visible(fn ($record) => $record !== null),

                    Placeholder::make('low_yield_warning')
                        ->label('Yield Status')
                        ->content(fn ($record) => $record && $record->isLowYield() 
                            ? '<span class="text-red-600 font-semibold">Low Yield (< 85%)</span>'
                            : ($record && $record->yield_percentage 
                                ? '<span class="text-green-600 font-semibold">Normal Yield</span>'
                                : 'N/A'))
                        ->visible(fn ($record) => $record !== null),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->visible(fn ($record) => $record !== null),

            Section::make('Quality & Notes')
                ->schema([
                    Select::make('supervisor_id')
                        ->label('Supervisor')
                        ->relationship('supervisor', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Supervisor who approved this harvest'),

                    Textarea::make('quality_notes')
                        ->label('Quality Notes')
                        ->rows(3)
                        ->helperText('Observations about quality, appearance, etc.'),

                    Textarea::make('harvest_notes')
                        ->label('Harvest Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Status')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->native(false)
                        ->disabled(fn ($record) => $record && $record->status === 'completed'),
                ]),
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
                    ->url(fn (Harvest $record) => BatchResource::getUrl('edit', ['record' => $record->batch_id]))
                    ->openUrlInNewTab(),

                TextColumn::make('harvest_date')
                    ->label('Harvest Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('strain_name')
                    ->label('Strain')
                    ->getStateUsing(fn (Harvest $record) => $record->batch->strain->name ?? 'N/A')
                    ->badge()
                    ->color('info'),

                TextColumn::make('wet_weight')
                    ->label('Wet Weight (g)')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 2) : 'N/A')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('dry_weight')
                    ->label('Dry Weight (g)')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 2) : 'N/A')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('yield_percentage')
                    ->label('Yield %')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) . '%' : 'N/A')
                    ->badge()
                    ->color(fn (?string $state) => $state && (float) $state < 85 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('low_yield_deviation_raised')
                    ->label('Low Yield Flag')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->visible(fn ($record) => $record->yield_percentage !== null),

                IconColumn::make('lots_created')
                    ->label('Lots Created')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('harvestedBy.name')
                    ->label('Harvested By')
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

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                TernaryFilter::make('low_yield_deviation_raised')
                    ->label('Low Yield')
                    ->placeholder('All harvests')
                    ->trueLabel('Low yield only')
                    ->falseLabel('Normal yield only'),

                TernaryFilter::make('lots_created')
                    ->label('Lots Created')
                    ->placeholder('All harvests')
                    ->trueLabel('Lots created')
                    ->falseLabel('Lots not created'),
            ])
            ->actions([
                Action::make('create_lots')
                    ->label('Create Lots')
                    ->icon('heroicon-o-cube')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Harvest $record) {
                        // This will be implemented when Inventory module is ready
                        $record->update(['lots_created' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Lots Created')
                            ->body('Material lots will be created from this harvest')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Harvest $record) => 
                        $record->status === 'completed' && 
                        !$record->lots_created &&
                        (auth()->user()?->can('manage inventory') || auth()->user()?->hasRole('Administrator'))
                    ),

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
            ->defaultSort('harvest_date', 'desc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHarvests::route('/'),
            'create' => Pages\CreateHarvest::route('/create'),
            'edit' => Pages\EditHarvest::route('/{record}/edit'),
        ];
    }
}

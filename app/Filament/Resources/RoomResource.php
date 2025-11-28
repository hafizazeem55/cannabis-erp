<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn as TableTextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Rooms';
    protected static ?int $navigationSort = 5;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Room Information')
                ->schema([
                    Select::make('facility_id')
                        ->label('Facility')
                        ->relationship('facility', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->default(fn () => request()->integer('facility_id')),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Room Name'),

                    TextInput::make('code')
                        ->maxLength(50)
                        ->label('Room Code')
                        ->helperText('Optional unique code'),

                    Select::make('type')
                        ->label('Room Type')
                        ->required()
                        ->options([
                            'nursery' => 'Nursery',
                            'veg' => 'Vegetative',
                            'flower' => 'Flower',
                            'cure' => 'Cure',
                            'packaging' => 'Packaging',
                            'warehouse' => 'Warehouse',
                            'quarantine' => 'Quarantine',
                        ])
                        ->native(false),

                    TextInput::make('capacity')
                        ->label('Capacity')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->helperText('Maximum plant/batch capacity'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Environmental Thresholds')
                ->description('Set minimum and maximum values for environmental monitoring')
                ->schema([
                    TextInput::make('temperature_min')
                        ->label('Temperature Min (°C)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('temperature_max')
                        ->label('Temperature Max (°C)')
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

                    TextInput::make('co2_min')
                        ->label('CO2 Min (ppm)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('co2_max')
                        ->label('CO2 Max (ppm)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ph_min')
                        ->label('pH Min')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ph_max')
                        ->label('pH Max')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ec_min')
                        ->label('EC Min (mS/cm)')
                        ->numeric()
                        ->step(0.01),

                    TextInput::make('ec_max')
                        ->label('EC Max (mS/cm)')
                        ->numeric()
                        ->step(0.01),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive rooms cannot be assigned to new batches'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TableTextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TableTextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TableTextColumn::make('facility.name')
                    ->label('Facility')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TableTextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nursery' => 'success',
                        'veg' => 'info',
                        'flower' => 'warning',
                        'cure' => 'gray',
                        'packaging' => 'primary',
                        'warehouse' => 'secondary',
                        'quarantine' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nursery' => 'Nursery',
                        'veg' => 'Vegetative',
                        'flower' => 'Flower',
                        'cure' => 'Cure',
                        'packaging' => 'Packaging',
                        'warehouse' => 'Warehouse',
                        'quarantine' => 'Quarantine',
                        default => $state,
                    }),

                TableTextColumn::make('capacity')
                    ->label('Capacity')
                    ->sortable()
                    ->alignEnd(),

                TableTextColumn::make('current_utilization')
                    ->label('Current')
                    ->getStateUsing(fn (Room $record) => $record->current_utilization)
                    ->alignEnd(),

                TableTextColumn::make('utilization_percentage')
                    ->label('Utilization %')
                    ->getStateUsing(fn (Room $record) => number_format($record->utilization_percentage, 1) . '%')
                    ->badge()
                    ->color(fn (Room $record) => $record->utilization_percentage > 90 ? 'danger' : ($record->utilization_percentage > 75 ? 'warning' : 'success'))
                    ->sortable(),

                TableTextColumn::make('batches_count')
                    ->label('Active Batches')
                    ->counts('activeBatches')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('facility_id')
                    ->label('Facility')
                    ->relationship('facility', 'name')
                    ->searchable(),

                SelectFilter::make('type')
                    ->label('Room Type')
                    ->options([
                        'nursery' => 'Nursery',
                        'veg' => 'Vegetative',
                        'flower' => 'Flower',
                        'cure' => 'Cure',
                        'packaging' => 'Packaging',
                        'warehouse' => 'Warehouse',
                        'quarantine' => 'Quarantine',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All rooms')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
            ->defaultSort('name');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}


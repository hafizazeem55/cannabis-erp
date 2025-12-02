<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentalThresholdResource\Pages;
use App\Models\EnvironmentalThreshold;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class EnvironmentalThresholdResource extends Resource
{
    protected static ?string $model = EnvironmentalThreshold::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Env Thresholds';
    protected static ?string $modelLabel = 'Environmental Threshold';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        $stageOptions = collect(EnvironmentalThreshold::STAGES)->mapWithKeys(fn ($stage) => [$stage => ucfirst($stage)]);
        $parameterOptions = [
            'temperature' => 'Temperature (°C)',
            'humidity' => 'Humidity (%)',
            'co2' => 'CO2 (ppm)',
            'ph' => 'pH',
            'ec' => 'EC (mS/cm)',
        ];

        return $form->schema([
            Section::make('Threshold Definition')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('stage')
                            ->label('Batch Stage')
                            ->options($stageOptions)
                            ->required()
                            ->native(false),
                        Select::make('parameter')
                            ->label('Parameter')
                            ->options($parameterOptions)
                            ->required()
                            ->native(false),
                        Select::make('severity')
                            ->label('Severity')
                            ->options([
                                'standard' => 'Standard',
                                'warning' => 'Warning',
                                'critical' => 'Critical',
                            ])
                            ->required()
                            ->default('standard')
                            ->native(false),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('min_value')
                            ->label('Min')
                            ->numeric()
                            ->required()
                            ->rule('lte:max_value')
                            ->prefixIcon('heroicon-o-arrow-small-left'),
                        TextInput::make('target_value')
                            ->label('Target')
                            ->numeric()
                            ->required()
                            ->rules(['gte:min_value', 'lte:max_value'])
                            ->prefixIcon('heroicon-o-sparkles'),
                        TextInput::make('max_value')
                            ->label('Max')
                            ->numeric()
                            ->required()
                            ->rule('gte:min_value')
                            ->prefixIcon('heroicon-o-arrow-small-right'),
                        TextInput::make('tolerance_percent')
                            ->label('Tolerance %')
                            ->numeric()
                            ->required()
                            ->default(5)
                            ->helperText('Allowed deviation around target'),
                    ]),
                    Grid::make(1)->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Add any notes or SOP details...')
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $stageOptions = collect(EnvironmentalThreshold::STAGES)->mapWithKeys(fn ($stage) => [$stage => ucfirst($stage)]);
        $parameterOptions = [
            'temperature' => 'Temperature',
            'humidity' => 'Humidity',
            'co2' => 'CO2',
            'ph' => 'pH',
            'ec' => 'EC',
        ];

        return $table
            ->columns([
                TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $stageOptions[$state] ?? $state)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('parameter')
                    ->label('Parameter')
                    ->formatStateUsing(fn ($state) => $parameterOptions[$state] ?? $state)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('range')
                    ->label('Range')
                    ->getStateUsing(fn ($record) => number_format((float) $record->min_value, 2) . ' - ' . number_format((float) $record->max_value, 2))
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('target_value')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('tolerance_percent')
                    ->label('Tolerance %')
                    ->formatStateUsing(fn ($state) => '±' . number_format((float) $state, 2) . '%')
                    ->sortable(),
                BadgeColumn::make('severity')
                    ->label('Severity')
                    ->colors([
                        'success' => 'standard',
                        'warning' => 'warning',
                        'danger' => 'critical',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->options($stageOptions),
                SelectFilter::make('parameter')
                    ->options($parameterOptions),
                SelectFilter::make('severity')
                    ->options([
                        'standard' => 'Standard',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('stage');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvironmentalThresholds::route('/'),
            'create' => Pages\CreateEnvironmentalThreshold::route('/create'),
            'edit' => Pages\EditEnvironmentalThreshold::route('/{record}/edit'),
        ];
    }
}

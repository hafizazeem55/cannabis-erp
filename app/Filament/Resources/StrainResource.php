<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StrainResource\Pages;
use App\Models\Strain;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class StrainResource extends Resource
{
    protected static ?string $model = Strain::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Strains';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Strain Information')
                ->schema([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->relationship('organization', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Strain Name')
                        ->placeholder('e.g., Blue Dream, OG Kush'),

                    TextInput::make('code')
                        ->maxLength(50)
                        ->label('Strain Code')
                        ->helperText('Optional unique code'),

                    Select::make('type')
                        ->label('Strain Type')
                        ->options([
                            'indica' => 'Indica',
                            'sativa' => 'Sativa',
                            'hybrid' => 'Hybrid',
                        ])
                        ->native(false),

                    Textarea::make('genetics')
                        ->label('Genetics')
                        ->rows(2)
                        ->placeholder('e.g., Blueberry x Haze')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Cannabinoid Profile (Expected Ranges)')
                ->description('Expected THC and CBD percentages for this strain')
                ->schema([
                    TextInput::make('thc_min')
                        ->label('THC Min (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100),

                    TextInput::make('thc_max')
                        ->label('THC Max (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100),

                    TextInput::make('cbd_min')
                        ->label('CBD Min (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100),

                    TextInput::make('cbd_max')
                        ->label('CBD Max (%)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(100),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),

            Section::make('Yield Benchmarks')
                ->description('Expected yield and growth characteristics')
                ->schema([
                    TextInput::make('expected_yield_per_plant')
                        ->label('Expected Yield per Plant (grams)')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Average expected yield per plant'),

                    TextInput::make('expected_vegetative_days')
                        ->label('Expected Vegetative Days')
                        ->numeric()
                        ->integer()
                        ->helperText('Average days in vegetative stage'),

                    TextInput::make('expected_flowering_days')
                        ->label('Expected Flowering Days')
                        ->numeric()
                        ->integer()
                        ->helperText('Average days in flowering stage'),

                    Textarea::make('growth_notes')
                        ->label('Growth Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('nutrient_requirements')
                        ->label('Nutrient Requirements')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive strains cannot be used for new batches'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Strain Catalog')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('genetics')
                    ->label('Genetics')
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->genetics ?: null)
                    ->placeholder('N/A'),

                TextColumn::make('expected_flowering_days')
                    ->label('Flower Time')
                    ->formatStateUsing(fn ($state): string => $state ? $state . ' days' : 'N/A')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->description ?: null)
                    ->placeholder('N/A'),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'indica' => 'purple',
                        'sativa' => 'success',
                        'hybrid' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'N/A')
                    ->toggleable(),

                TextColumn::make('thc_min')
                    ->label('THC Range')
                    ->formatStateUsing(fn ($record) => $record->thc_min && $record->thc_max 
                        ? number_format($record->thc_min, 1) . '% - ' . number_format($record->thc_max, 1) . '%'
                        : 'N/A')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('expected_yield_per_plant')
                    ->label('Expected Yield/Plant')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) . 'g' : 'N/A')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('batches_count')
                    ->label('Active Batches')
                    ->counts('activeBatches')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->badge()
                    ->color('info')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator'))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Strain Type')
                    ->options([
                        'indica' => 'Indica',
                        'sativa' => 'Sativa',
                        'hybrid' => 'Hybrid',
                    ]),

                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->relationship('organization', 'name')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All strains')
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
            'index' => Pages\ListStrains::route('/'),
            'create' => Pages\CreateStrain::route('/create'),
            'edit' => Pages\EditStrain::route('/{record}/edit'),
        ];
    }
}


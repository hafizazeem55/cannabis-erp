<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource;
use App\Filament\Resources\GrowthCycleResource\Pages;
use App\Filament\Resources\GrowthCycleResource\Widgets\GrowthCycleStatsOverview;
use App\Models\GrowthCycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class GrowthCycleResource extends Resource
{
    protected static ?string $model = GrowthCycle::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Growth Cycles';
    protected static ?int $navigationSort = 0;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Cycle Information')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('organization_id')
                            ->label('Organization')
                            ->relationship('organization', 'name')
                            ->visible(fn () => auth()->user()?->hasRole('Administrator'))
                            ->searchable()
                            ->preload(),

                        Select::make('facility_id')
                            ->label('Facility')
                            ->relationship('facility', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),

                    TextInput::make('name')
                        ->label('Cycle Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., OG Kush - January 2026'),

                    Select::make('status')
                        ->options([
                            'planning' => 'Planning',
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->label('Status')
                        ->default('planning')
                        ->native(false),

                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),

                        DatePicker::make('expected_end_date')
                            ->label('Expected End Date')
                            ->helperText('Estimated end date for this cycle'),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('primary_strain_id')
                            ->label('Primary Strain')
                            ->relationship('primaryStrain', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Primary strain associated with this cycle'),

                        DatePicker::make('actual_end_date')
                            ->label('Actual End Date')
                            ->helperText('Set when the cycle is completed'),
                    ]),
                ])
                ->columns(1),

            Section::make('Strains & Notes')
                ->schema([
                    Select::make('strains')
                        ->label('Strains')
                        ->relationship('strains', 'name')
                        ->multiple()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->helperText('Select one or more strains that are part of this cycle'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->placeholder('Add any planning notes or key objectives for this cycle'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Growth Cycles')
            ->description(fn () => GrowthCycle::query()
                ->when(
                    ! auth()->user()?->hasRole('Administrator'),
                    fn (Builder $query) => $query->where('organization_id', auth()->user()?->organization_id)
                )
                ->count() . ' total growth cycles'
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Cycle Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('strain_names')
                    ->label('Strain')
                    ->toggleable()
                    ->wrap()
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('facility.name')
                    ->label('Facility')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'planning',
                        'success' => ['active', 'completed'],
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-sparkles' => 'planning',
                        'heroicon-o-play' => 'active',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '—'),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('expected_end_date')
                    ->label('Expected End')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('facility_id')
                    ->label('Facility')
                    ->relationship('facility', 'name')
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->url(fn (GrowthCycle $record) => static::getUrl('view', ['record' => $record])),
                    Action::make('edit')
                        ->label('Edit Cycle')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn (GrowthCycle $record) => static::getUrl('edit', ['record' => $record])),
                    Action::make('add_batch')
                        ->label('Add Batches')
                        ->icon('heroicon-o-plus-circle')
                        ->color('info')
                        ->url(fn (GrowthCycle $record) => BatchResource::getUrl('create', ['growth_cycle_id' => $record->getKey()])),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getWidgets(): array
    {
        return [
            GrowthCycleStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrowthCycles::route('/'),
            'create' => Pages\CreateGrowthCycle::route('/create'),
            'view' => Pages\ViewGrowthCycle::route('/{record}'),
            'edit' => Pages\EditGrowthCycle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()?->hasRole('Administrator')) {
            $query->where('organization_id', auth()->user()?->organization_id);
        }

        return $query->withCount('batches');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator');
    }
}


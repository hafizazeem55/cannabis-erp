<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?string $navigationLabel = 'Facilities';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Facility Information')
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
                        ->label('Facility Name'),

                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->label('Facility Code')
                        ->helperText('Unique code for this facility'),
                ])
                ->columns(2),

            Section::make('Address')
                ->schema([
                    Textarea::make('address')
                        ->label('Street Address')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextInput::make('city')
                        ->maxLength(100),

                    TextInput::make('state')
                        ->maxLength(100),

                    TextInput::make('country')
                        ->maxLength(100),

                    TextInput::make('postal_code')
                        ->maxLength(20)
                        ->label('Postal Code'),
                ])
                ->columns(2),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive facilities cannot be used for new batches'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->sortable()
                    ->badge()
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rooms_count')
                    ->label('Rooms')
                    ->counts('rooms')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('tunnels_count')
                    ->label('Tunnels')
                    ->counts('tunnels')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->relationship('organization', 'name')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All facilities')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Facility $record) => static::getUrl('view', ['record' => $record])),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                ViewEntry::make('facility_overview')
                    ->label('')
                    ->view('filament.resources.facility-resource.view')
                    ->viewData(fn (Facility $record) => [
                        'record' => $record->loadMissing(['rooms', 'tunnels', 'organization']),
                        'rooms' => $record->rooms()->orderBy('name')->get(),
                        'tunnels' => $record->tunnels()->orderBy('name')->get(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
            'view' => Pages\ViewFacility::route('/{record}'),
        ];
    }
}


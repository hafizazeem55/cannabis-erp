<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiClassificationResultResource\Pages;
use App\Models\AiClassificationResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AiClassificationResultResource extends Resource
{
    protected static ?string $model = AiClassificationResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Plant Classifications';

    protected static ?string $navigationGroup = 'AI Tools';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('ai.use') || auth()->user()->hasRole('Administrator');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Classification Details')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Plant Image')
                            ->image()
                            ->disk('public')
                            ->directory('ai/classifications')
                            ->visibility('public')
                            ->required()
                            ->imagePreviewHeight('250')
                            ->imageEditor()
                            ->downloadable(),

                        Forms\Components\Select::make('batch_id')
                            ->label('Batch')
                            ->relationship('batch', 'batch_code')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Classified By')
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('AI Classification Results')
                    ->schema([
                        Forms\Components\TextInput::make('growth_stage')
                            ->label('Growth Stage')
                            ->disabled(),

                        Forms\Components\TextInput::make('health_status')
                            ->label('Health Status')
                            ->disabled(),

                        Forms\Components\TextInput::make('leaf_issue')
                            ->label('Leaf Issue')
                            ->disabled(),

                        Forms\Components\TextInput::make('strain_type')
                            ->label('Strain Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('growth_stage_confidence')
                            ->label('Growth Stage Confidence')
                            ->suffix('%')
                            ->disabled(),

                        Forms\Components\TextInput::make('health_status_confidence')
                            ->label('Health Status Confidence')
                            ->suffix('%')
                            ->disabled(),

                        Forms\Components\Textarea::make('metadata')
                            ->label('Additional Details')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->width(80)
                    ->height(80),

                Tables\Columns\TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('growth_stage')
                    ->label('Growth Stage')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'seedling' => 'info',
                        'vegetative' => 'success',
                        'pre_flowering' => 'warning',
                        'flowering' => 'primary',
                        'late_flowering' => 'danger',
                        'harvest_ready' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('health_status')
                    ->label('Health')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'healthy' => 'success',
                        'stressed' => 'warning',
                        'diseased' => 'danger',
                        'dying' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('leaf_issue')
                    ->label('Leaf Issue')
                    ->badge()
                    ->color('warning')
                    ->default('None')
                    ->formatStateUsing(fn ($state) => $state ?? 'None'),

                Tables\Columns\TextColumn::make('strain_type')
                    ->label('Strain Type')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('growth_stage_confidence')
                    ->label('Confidence')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Classified By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('batch_id')
                    ->label('Batch')
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('growth_stage')
                    ->label('Growth Stage')
                    ->options([
                        'seedling' => 'Seedling',
                        'vegetative' => 'Vegetative',
                        'pre_flowering' => 'Pre-Flowering',
                        'flowering' => 'Flowering',
                        'late_flowering' => 'Late Flowering',
                        'harvest_ready' => 'Harvest Ready',
                    ]),

                Tables\Filters\SelectFilter::make('health_status')
                    ->label('Health Status')
                    ->options([
                        'healthy' => 'Healthy',
                        'stressed' => 'Stressed',
                        'diseased' => 'Diseased',
                        'dying' => 'Dying',
                    ]),

                Tables\Filters\SelectFilter::make('strain_type')
                    ->label('Strain Type')
                    ->options([
                        'indica' => 'Indica',
                        'sativa' => 'Sativa',
                        'hybrid' => 'Hybrid',
                    ]),

                Tables\Filters\Filter::make('high_confidence')
                    ->label('High Confidence (≥80%)')
                    ->query(fn (Builder $query): Builder => $query->where('growth_stage_confidence', '>=', 80)),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiClassificationResults::route('/'),
            'create' => Pages\CreateAiClassificationResult::route('/create'),
            'view' => Pages\ViewAiClassificationResult::route('/{record}'),
        ];
    }
}

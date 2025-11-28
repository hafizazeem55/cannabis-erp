<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiAnomalyReportResource\Pages;
use App\Models\AiAnomalyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AiAnomalyReportResource extends Resource
{
    protected static ?string $model = AiAnomalyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'AI Anomaly Reports';

    protected static ?string $navigationGroup = 'AI Tools';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
                    ->schema([
                        Forms\Components\Select::make('batch_id')
                            ->relationship('batch', 'batch_code')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Plant Image')
                            ->image()
                            ->required()
                            ->maxSize(10240),
                    ])->columns(3),

                Forms\Components\Section::make('Detection Results')
                    ->schema([
                        Forms\Components\Toggle::make('is_anomaly')
                            ->label('Anomaly Detected')
                            ->disabled(),
                        Forms\Components\TextInput::make('confidence')
                            ->numeric()
                            ->disabled()
                            ->suffix('%')
                            ->formatStateUsing(fn($state) => $state ? round($state * 100, 2) : 0),
                        Forms\Components\Select::make('severity')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'critical' => 'Critical',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('detected_issue')
                            ->disabled(),
                        Forms\Components\Textarea::make('issue_description')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\Textarea::make('recommended_action')
                            ->disabled()
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Review')
                    ->schema([
                        Forms\Components\Toggle::make('reviewed')
                            ->label('Mark as Reviewed'),
                        Forms\Components\Textarea::make('review_notes')
                            ->rows(3),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_anomaly')
                    ->label('Anomaly')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('detected_issue')
                    ->label('Issue')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'critical',
                    ]),
                Tables\Columns\TextColumn::make('confidence')
                    ->label('Confidence')
                    ->formatStateUsing(fn($state) => $state ? round($state * 100, 2) . '%' : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('reviewed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Detected At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_anomaly')
                    ->options([
                        '1' => 'Anomaly Detected',
                        '0' => 'No Anomaly',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('reviewed')
                    ->options([
                        '1' => 'Reviewed',
                        '0' => 'Pending Review',
                    ]),
                Tables\Filters\Filter::make('batch_id')
                    ->form([
                        Forms\Components\Select::make('batch_id')
                            ->label('Batch')
                            ->relationship('batch', 'batch_code')
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['batch_id'],
                            fn (Builder $query, $batchId): Builder => $query->where('batch_id', $batchId),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('review')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn($record) => !$record->reviewed)
                    ->form([
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Review Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'reviewed' => true,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAiAnomalyReports::route('/'),
            'create' => Pages\CreateAiAnomalyReport::route('/create'),
            'view' => Pages\ViewAiAnomalyReport::route('/{record}'),
            'edit' => Pages\EditAiAnomalyReport::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('ai.use') 
            || auth()->user()->hasRole('Administrator');
    }
}

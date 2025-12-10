<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class BatchProgressWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Active Batches Progress';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Batch::query()
                    ->where('is_active', true)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->with(['strain', 'room'])
                    ->orderBy('progress_percentage', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (Batch $record) => \App\Filament\Resources\BatchResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                TextColumn::make('strain.name')
                    ->label('Strain')
                    ->badge()
                    ->color('info'),

                TextColumn::make('room.name')
                    ->label('Room')
                    ->badge()
                    ->color('success'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cloning', 'clone', 'propagation' => 'gray',
                        'vegetative' => 'info',
                        'flowering', 'flower' => 'warning',
                        'harvest' => 'success',
                        'drying' => 'warning',
                        'curing' => 'info',
                        'packaging', 'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cloning' => 'Cloning',
                        'clone' => 'Clone',
                        'propagation' => 'Propagation',
                        'vegetative' => 'Vegetative',
                        'flowering', 'flower' => 'Flowering',
                        'harvest' => 'Harvest',
                        'drying' => 'Drying',
                        'curing' => 'Curing',
                        'packaging' => 'Packaging',
                        'completed' => 'Completed',
                        default => ucfirst($state),
                    }),

                TextColumn::make('current_plant_count')
                    ->label('Plants')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format((float) $state, 1) . '%' : '0%')
                    ->badge()
                    ->color(fn (?string $state) => (float) $state > 90 ? 'success' : ((float) $state > 50 ? 'info' : 'warning'))
                    ->sortable(),

                TextColumn::make('expected_harvest_date')
                    ->label('Expected Harvest')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->expected_harvest_date && $record->expected_harvest_date->isPast() ? 'danger' : null),
            ])
            ->defaultSort('progress_percentage', 'desc');
    }
}


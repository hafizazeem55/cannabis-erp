<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UpcomingHarvestsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Upcoming Harvests (Next 7 Days)';

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
                    ->where('status', 'flower')
                    ->whereNotNull('expected_harvest_date')
                    ->where('expected_harvest_date', '<=', now()->addDays(7))
                    ->where('expected_harvest_date', '>=', now())
                    ->with(['strain', 'room'])
                    ->orderBy('expected_harvest_date', 'asc')
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

                TextColumn::make('current_plant_count')
                    ->label('Plants')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('expected_yield')
                    ->label('Expected Yield (g)')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 2) : 'N/A')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('expected_harvest_date')
                    ->label('Harvest Date')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        if (!$record || !$record->expected_harvest_date) {
                            return 'gray';
                        }
                        $daysUntil = now()->diffInDays($record->expected_harvest_date, false);
                        if ($daysUntil < 0) return 'danger';
                        if ($daysUntil <= 2) return 'warning';
                        return 'success';
                    })
                    ->formatStateUsing(function ($record) {
                        if (!$record || !$record->expected_harvest_date) {
                            return 'N/A';
                        }
                        $daysUntil = now()->diffInDays($record->expected_harvest_date, false);
                        return $record->expected_harvest_date->format('M d, Y') . ' (' . $daysUntil . ' days)';
                    }),

                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format($state, 1) . '%' : '0%')
                    ->badge()
                    ->color(fn (?string $state) => (float) $state > 90 ? 'success' : ((float) $state > 75 ? 'info' : 'warning'))
                    ->sortable(),
            ])
            ->defaultSort('expected_harvest_date', 'asc');
    }
}


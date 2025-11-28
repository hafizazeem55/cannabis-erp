<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class RoomUtilizationWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Room Utilization';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator');
    }

    public function table(Table $table): Table
    {
        $utilizationOrderExpression = $this->getUtilizationOrderExpression();

        return $table
            ->query(
                Room::query()
                    ->where('is_active', true)
                    ->whereIn('type', ['nursery', 'veg', 'flower'])
                    ->with('facility')
                    ->orderByRaw("{$utilizationOrderExpression} desc")
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Room')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (Room $record) => \App\Filament\Resources\RoomResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                TextColumn::make('facility.name')
                    ->label('Facility')
                    ->badge()
                    ->color('info')
                    ->visible(fn () => auth()->user()?->hasRole('Administrator')),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nursery' => 'success',
                        'veg' => 'info',
                        'flower' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nursery' => 'Nursery',
                        'veg' => 'Vegetative',
                        'flower' => 'Flower',
                        default => ucfirst($state),
                    }),

                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('current_utilization')
                    ->label('Current')
                    ->getStateUsing(fn (Room $record) => $record->current_utilization)
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('available_capacity')
                    ->label('Available')
                    ->getStateUsing(fn (Room $record) => $record->available_capacity)
                    ->badge()
                    ->color(fn (Room $record) => $record->available_capacity < 10 ? 'danger' : ($record->available_capacity < 50 ? 'warning' : 'success'))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('utilization_percentage')
                    ->label('Utilization %')
                    ->formatStateUsing(fn (Room $record) => number_format($record->utilization_percentage, 1) . '%')
                    ->badge()
                    ->color(fn (Room $record) => $record->utilization_percentage > 90 ? 'danger' : ($record->utilization_percentage > 75 ? 'warning' : 'success'))
                    ->sortable(
                        true,
                        function (Builder $query, string $direction) use ($utilizationOrderExpression) {
                            return $query->orderByRaw(
                                $utilizationOrderExpression . ' ' . (strtolower($direction) === 'desc' ? 'desc' : 'asc')
                            );
                        }
                    ),

                TextColumn::make('activeBatches_count')
                    ->label('Active Batches')
                    ->counts('activeBatches')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ]);
    }

    protected function getUtilizationOrderExpression(): string
    {
        return "(CASE WHEN rooms.capacity = 0 THEN 0 ELSE (((SELECT COALESCE(SUM(batches.current_plant_count), 0) FROM batches WHERE batches.room_id = rooms.id AND batches.is_active = 1 AND batches.status NOT IN ('completed','cancelled') AND batches.deleted_at IS NULL) / NULLIF(rooms.capacity, 0)) * 100) END)";
    }
}


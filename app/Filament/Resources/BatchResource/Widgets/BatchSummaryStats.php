<?php

namespace App\Filament\Resources\BatchResource\Widgets;

use App\Models\Batch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class BatchSummaryStats extends BaseWidget
{
    protected static ?string $pollingInterval = '45s';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->can('manage cultivation')
            || $user?->can('view cultivation')
            || $user?->hasRole('Administrator');
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $activeBatches = Batch::where('is_active', true)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $floweringBatches = Batch::where('is_active', true)
            ->whereIn('status', ['flowering', 'flower'])
            ->count();

        $harvestReadyBatches = Batch::where('is_active', true)
            ->where('status', 'harvest')
            ->count();

        $totalPlants = Batch::where('is_active', true)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->sum('current_plant_count');

        return [
            Card::make('Active Batches', $activeBatches)
                ->description('Currently in cultivation')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
            Card::make('In Flowering', $floweringBatches)
                ->description('Batches in flowering stage')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
            Card::make('Ready for Harvest', $harvestReadyBatches)
                ->description('Awaiting harvest actions')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('info'),
            Card::make('Total Plants', number_format($totalPlants))
                ->description('Plants inside active batches')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}

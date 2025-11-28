<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use App\Models\Strain;
use App\Models\Room;
use App\Models\Harvest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class CultivationStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator');
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $cards = [];

        // Active Batches Card
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $activeBatches = Batch::where('is_active', true)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();

            $cards[] = Card::make('Active Batches', $activeBatches)
                ->description('Currently in cultivation')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success')
                ->chart($this->getActiveBatchesChart());
        }

        // Batches by Stage
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $cloneCount = Batch::where('status', 'clone')->where('is_active', true)->count();
            $vegCount = Batch::where('status', 'vegetative')->where('is_active', true)->count();
            $flowerCount = Batch::where('status', 'flower')->where('is_active', true)->count();

            $cards[] = Card::make('Batches by Stage', "Clone: {$cloneCount} | Veg: {$vegCount} | Flower: {$flowerCount}")
                ->description('Current stage distribution')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info');
        }

        // Total Plants
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $totalPlants = Batch::where('is_active', true)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->sum('current_plant_count');

            $cards[] = Card::make('Total Plants', number_format($totalPlants))
                ->description('Currently in cultivation')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning')
                ->chart($this->getPlantCountChart());
        }

        // Upcoming Harvests
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $upcomingHarvests = Batch::where('is_active', true)
                ->where('status', 'flower')
                ->whereNotNull('expected_harvest_date')
                ->where('expected_harvest_date', '<=', now()->addDays(7))
                ->count();

            $cards[] = Card::make('Upcoming Harvests', $upcomingHarvests)
                ->description('Within next 7 days')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('danger')
                ->chart($this->getUpcomingHarvestsChart());
        }

        // Room Utilization
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $avgUtilization = Room::where('is_active', true)
                ->whereIn('type', ['nursery', 'veg', 'flower'])
                ->get()
                ->avg(fn ($room) => $room->utilization_percentage);

            $cards[] = Card::make('Avg Room Utilization', number_format($avgUtilization ?? 0, 1) . '%')
                ->description('Average across cultivation rooms')
                ->descriptionIcon('heroicon-m-home')
                ->color('primary');
        }

        // Total Strains
        if ($user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator')) {
            $totalStrains = Strain::where('is_active', true)->count();
            
            $cards[] = Card::make('Active Strains', $totalStrains)
                ->description('Available for cultivation')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('gray');
        }

        return $cards;
    }

    protected function getActiveBatchesChart(): array
    {
        // Return last 7 days of batch creation counts
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Batch::where('is_active', true)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    protected function getPlantCountChart(): array
    {
        // Return last 7 days of total plant counts
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $total = Batch::where('is_active', true)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('created_at', '<=', $date)
                ->sum('current_plant_count');
            $data[] = (int) $total;
        }
        return $data;
    }

    protected function getUpcomingHarvestsChart(): array
    {
        // Return next 7 days of expected harvests
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            $count = Batch::where('is_active', true)
                ->where('status', 'flower')
                ->whereNotNull('expected_harvest_date')
                ->whereDate('expected_harvest_date', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }
}


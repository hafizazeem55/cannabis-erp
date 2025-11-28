<?php

namespace App\Filament\Resources\GrowthCycleResource\Widgets;

use App\Models\GrowthCycle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class GrowthCycleStatsOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $baseQuery = $this->getScopedQuery();

        $activeCount = (clone $baseQuery)->where('status', 'active')->count();
        $planningCount = (clone $baseQuery)->where('status', 'planning')->count();
        $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
        $totalCount = (clone $baseQuery)->count();

        return [
            Stat::make('Active Cycles', $activeCount)
                ->icon('heroicon-o-sparkles')
                ->description('Currently in progress')
                ->color('success'),

            Stat::make('Planning', $planningCount)
                ->icon('heroicon-o-calendar')
                ->description('Upcoming cultivation plans')
                ->color('info'),

            Stat::make('Completed', $completedCount)
                ->icon('heroicon-o-check-circle')
                ->description('Finished growth cycles')
                ->color('gray'),

            Stat::make('Total Cycles', $totalCount)
                ->icon('heroicon-o-circle-stack')
                ->description('All tracked growth cycles')
                ->color('primary'),
        ];
    }

    protected function getScopedQuery(): Builder
    {
        $query = GrowthCycle::query();

        if (! auth()->user()?->hasRole('Administrator')) {
            $query->where('organization_id', auth()->user()?->organization_id);
        }

        return $query;
    }
}


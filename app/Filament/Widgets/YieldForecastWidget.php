<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use App\Models\Harvest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class YieldForecastWidget extends ChartWidget
{
    protected static ?string $heading = 'Yield Forecast vs Actual';
    protected static ?int $sort = 6;
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator');
    }

    protected function getData(): array
    {
        // Get last 12 months of harvests
        $harvests = Harvest::where('status', 'completed')
            ->where('harvest_date', '>=', now()->subMonths(12))
            ->selectRaw('MONTH(harvest_date) as month, YEAR(harvest_date) as year, 
                AVG(expected_yield) as avg_expected, 
                AVG(actual_yield) as avg_actual')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = $harvests->map(fn ($h) => date('M Y', mktime(0, 0, 0, $h->month, 1, $h->year)))->toArray();
        $expected = $harvests->pluck('avg_expected')->toArray();
        $actual = $harvests->pluck('avg_actual')->toArray();

        // If no harvests, show upcoming batches forecast
        if (empty($expected)) {
            $upcomingBatches = Batch::where('is_active', true)
                ->whereIn('status', ['flowering', 'flower'])
                ->whereNotNull('expected_harvest_date')
                ->where('expected_harvest_date', '<=', now()->addMonths(3))
                ->selectRaw('MONTH(expected_harvest_date) as month, YEAR(expected_harvest_date) as year, 
                    SUM(expected_yield) as total_expected')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $labels = $upcomingBatches->map(fn ($b) => date('M Y', mktime(0, 0, 0, $b->month, 1, $b->year)))->toArray();
            $expected = $upcomingBatches->pluck('total_expected')->toArray();
            $actual = [];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Expected Yield (g)',
                    'data' => $expected,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Actual Yield (g)',
                    'data' => $actual,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}


<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BatchesByStageWidget extends ChartWidget
{
    protected static ?string $heading = 'Batches by Stage';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->can('view cultivation') || $user?->hasRole('Administrator');
    }

    protected function getData(): array
    {
        $stages = [
            'Clone/Propagation' => Batch::whereIn('status', ['clone', 'propagation'])->where('is_active', true)->count(),
            'Vegetative' => Batch::where('status', 'vegetative')->where('is_active', true)->count(),
            'Flower' => Batch::where('status', 'flower')->where('is_active', true)->count(),
            'Harvest' => Batch::where('status', 'harvest')->where('is_active', true)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Batches',
                    'data' => array_values($stages),
                    'backgroundColor' => [
                        'rgb(156, 163, 175)', // gray for clone
                        'rgb(59, 130, 246)', // blue for veg
                        'rgb(251, 191, 36)', // yellow for flower
                        'rgb(34, 197, 94)',  // green for harvest
                    ],
                ],
            ],
            'labels' => array_keys($stages),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}


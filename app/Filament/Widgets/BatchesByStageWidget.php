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
            'Cloning' => Batch::whereIn('status', ['cloning', 'clone', 'propagation'])->where('is_active', true)->count(),
            'Vegetative' => Batch::where('status', 'vegetative')->where('is_active', true)->count(),
            'Flowering' => Batch::whereIn('status', ['flowering', 'flower'])->where('is_active', true)->count(),
            'Harvest' => Batch::where('status', 'harvest')->where('is_active', true)->count(),
            'Drying' => Batch::where('status', 'drying')->where('is_active', true)->count(),
            'Curing' => Batch::where('status', 'curing')->where('is_active', true)->count(),
            'Packaging' => Batch::where('status', 'packaging')->where('is_active', true)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Batches',
                    'data' => array_values($stages),
                    'backgroundColor' => [
                        'rgb(156, 163, 175)', // gray for cloning
                        'rgb(59, 130, 246)', // blue for veg
                        'rgb(251, 191, 36)', // yellow for flowering
                        'rgb(34, 197, 94)',  // green for harvest
                        'rgb(234, 179, 8)',  // amber for drying
                        'rgb(52, 211, 153)', // teal for curing
                        'rgb(99, 102, 241)', // indigo for packaging
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


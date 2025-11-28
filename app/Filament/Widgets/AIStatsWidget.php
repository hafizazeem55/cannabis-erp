<?php

namespace App\Filament\Widgets;

use App\Models\AiAnomalyReport;
use App\Models\AiClassificationResult;
use App\Models\Batch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AIStatsWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $user = auth()->user();

        // Check if user has AI permissions
        if (!$user->can('ai.use') && !$user->hasRole('Administrator')) {
            return [];
        }

        $totalAnomalies = AiAnomalyReport::count();
        $criticalAnomalies = AiAnomalyReport::where('severity', 'critical')
            ->where('reviewed', false)
            ->count();
        
        $totalClassifications = AiClassificationResult::count();
        $unhealthyPlants = AiClassificationResult::whereIn('health_status', ['stressed', 'diseased', 'dying'])
            ->count();

        return [
            Stat::make('Total AI Scans', $totalAnomalies + $totalClassifications)
                ->description('Anomaly detections + Classifications')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info'),

            Stat::make('Critical Anomalies', $criticalAnomalies)
                ->description('Requiring immediate attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalAnomalies > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.ai-anomaly-reports.index')),

            Stat::make('Unhealthy Plants', $unhealthyPlants)
                ->description('Stressed, diseased, or dying')
                ->descriptionIcon('heroicon-m-heart')
                ->color($unhealthyPlants > 0 ? 'warning' : 'success'),

            Stat::make('AI Knowledge Base', \App\Models\AiEmbedding::count())
                ->description('Embedded data chunks for chatbot')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('ai.use') 
            || auth()->user()->hasRole('Administrator');
    }
}

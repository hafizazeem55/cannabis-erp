<?php

namespace App\Filament\Widgets;

use App\Models\Strain;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StrainStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('manage cultivation') || $user?->hasRole('Administrator');
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $cards = [];

        if ($user?->can('manage cultivation') || $user?->hasRole('Administrator')) {
            // Total Strains
            $totalStrains = Strain::count();
            
            $cards[] = Card::make('Total Strains', $totalStrains)
                ->description('All strains in catalog')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary');

            // Active Status (Active Strains Count)
            $activeStrains = Strain::where('is_active', true)->count();
            
            $cards[] = Card::make('Active Status', $activeStrains)
                ->description('Active strains available for cultivation')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');

            // Average Flower Time
            $avgFlowerTime = Strain::whereNotNull('expected_flowering_days')
                ->where('expected_flowering_days', '>', 0)
                ->avg('expected_flowering_days');
            
            $cards[] = Card::make('Average Flower Time', $avgFlowerTime ? number_format($avgFlowerTime, 0) . ' days' : 'N/A')
                ->description('Average flowering period')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info');

            // Inactive Strains
            $inactiveStrains = Strain::where('is_active', false)->count();
            
            $cards[] = Card::make('Inactive Strains', $inactiveStrains)
                ->description('Not available for new batches')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger');
        }

        return $cards;
    }
}


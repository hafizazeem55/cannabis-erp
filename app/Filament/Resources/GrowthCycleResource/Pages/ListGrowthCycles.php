<?php

namespace App\Filament\Resources\GrowthCycleResource\Pages;

use App\Filament\Resources\GrowthCycleResource;
use App\Filament\Resources\GrowthCycleResource\Widgets\GrowthCycleStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrowthCycles extends ListRecords
{
    protected static string $resource = GrowthCycleResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            GrowthCycleStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Growth Cycle')
                ->icon('heroicon-o-plus'),
        ];
    }
}


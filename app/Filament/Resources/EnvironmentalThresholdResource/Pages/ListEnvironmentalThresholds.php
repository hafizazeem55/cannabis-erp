<?php

namespace App\Filament\Resources\EnvironmentalThresholdResource\Pages;

use App\Filament\Resources\EnvironmentalThresholdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnvironmentalThresholds extends ListRecords
{
    protected static string $resource = EnvironmentalThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Threshold')
                ->icon('heroicon-o-plus'),
        ];
    }
}

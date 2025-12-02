<?php

namespace App\Filament\Resources\EnvironmentalThresholdResource\Pages;

use App\Filament\Resources\EnvironmentalThresholdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnvironmentalThreshold extends EditRecord
{
    protected static string $resource = EnvironmentalThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

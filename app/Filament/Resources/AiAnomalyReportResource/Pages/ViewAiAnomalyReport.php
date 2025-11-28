<?php

namespace App\Filament\Resources\AiAnomalyReportResource\Pages;

use App\Filament\Resources\AiAnomalyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAiAnomalyReport extends ViewRecord
{
    protected static string $resource = AiAnomalyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

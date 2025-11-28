<?php

namespace App\Filament\Resources\AiAnomalyReportResource\Pages;

use App\Filament\Resources\AiAnomalyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAiAnomalyReports extends ListRecords
{
    protected static string $resource = AiAnomalyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

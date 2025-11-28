<?php

namespace App\Filament\Resources\AiAnomalyReportResource\Pages;

use App\Filament\Resources\AiAnomalyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAiAnomalyReport extends EditRecord
{
    protected static string $resource = AiAnomalyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

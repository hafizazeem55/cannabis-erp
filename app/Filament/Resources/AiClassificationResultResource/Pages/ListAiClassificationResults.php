<?php

namespace App\Filament\Resources\AiClassificationResultResource\Pages;

use App\Filament\Resources\AiClassificationResultResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListAiClassificationResults extends ListRecords
{
    protected static string $resource = AiClassificationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Classification'),
        ];
    }
}

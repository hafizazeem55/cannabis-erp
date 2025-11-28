<?php

namespace App\Filament\Resources\BatchLogResource\Pages;

use App\Filament\Resources\BatchLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBatchLogs extends ListRecords
{
    protected static string $resource = BatchLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }
}

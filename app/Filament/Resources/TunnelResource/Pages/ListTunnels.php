<?php

namespace App\Filament\Resources\TunnelResource\Pages;

use App\Filament\Resources\TunnelResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListTunnels extends ListRecords
{
    protected static string $resource = TunnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\TunnelResource\Pages;

use App\Filament\Resources\TunnelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTunnel extends EditRecord
{
    protected static string $resource = TunnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }
}

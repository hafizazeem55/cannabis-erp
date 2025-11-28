<?php

namespace App\Filament\Resources\StrainResource\Pages;

use App\Filament\Resources\StrainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStrain extends EditRecord
{
    protected static string $resource = StrainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }
}


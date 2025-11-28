<?php

namespace App\Filament\Resources\GrowthCycleResource\Pages;

use App\Filament\Resources\GrowthCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrowthCycle extends EditRecord
{
    protected static string $resource = GrowthCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('View Details')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->hasRole('Administrator')) {
            $data['organization_id'] = auth()->user()?->organization_id;
        }

        return $data;
    }
}


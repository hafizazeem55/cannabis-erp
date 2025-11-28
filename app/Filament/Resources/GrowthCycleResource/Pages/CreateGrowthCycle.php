<?php

namespace App\Filament\Resources\GrowthCycleResource\Pages;

use App\Filament\Resources\GrowthCycleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrowthCycle extends CreateRecord
{
    protected static string $resource = GrowthCycleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->hasRole('Administrator')) {
            $data['organization_id'] = auth()->user()?->organization_id;
        }

        $data['created_by'] = auth()->id();

        return $data;
    }
}


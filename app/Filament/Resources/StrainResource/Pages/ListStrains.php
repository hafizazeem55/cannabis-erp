<?php

namespace App\Filament\Resources\StrainResource\Pages;

use App\Filament\Resources\StrainResource;
use App\Filament\Widgets\StrainStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStrains extends ListRecords
{
    protected static string $resource = StrainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StrainStatsWidget::class,
        ];
    }
}


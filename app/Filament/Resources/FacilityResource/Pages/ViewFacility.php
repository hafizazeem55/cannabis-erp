<?php

namespace App\Filament\Resources\FacilityResource\Pages;

use App\Filament\Resources\FacilityResource;
use App\Filament\Resources\RoomResource;
use App\Filament\Resources\TunnelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFacility extends ViewRecord
{
    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('new_room')
                ->label('New Room')
                ->icon('heroicon-o-home')
                ->url(fn () => RoomResource::getUrl('create', ['facility_id' => $this->record?->id]))
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
            Actions\Action::make('new_tunnel')
                ->label('New Tunnel')
                ->icon('heroicon-o-puzzle-piece')
                ->url(fn () => TunnelResource::getUrl('create', ['facility_id' => $this->record?->id]))
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
            Actions\EditAction::make(),
        ];
    }
}

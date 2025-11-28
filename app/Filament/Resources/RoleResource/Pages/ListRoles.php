<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }
}

<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\AuditLog;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => static::getModel(),
            'model_id' => $this->record->id,
            'changes' => [
                'before' => null,
                'after' => [
                    'name' => $this->record->name,
                    'permissions' => $this->record->permissions->pluck('id')->toArray(),
                ],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }
}

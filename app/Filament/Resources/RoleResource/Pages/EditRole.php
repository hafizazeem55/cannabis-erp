<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\AuditLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => 
                    $record->name !== 'Administrator' && 
                    (auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator'))
                )
                ->disabled(fn ($record) => $record->users()->count() > 0),
        ];
    }

    protected function beforeSave(): void
    {
        $this->originalData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        $changes = [];
        foreach ($this->record->getDirty() as $key => $value) {
            $changes[$key] = [
                'before' => $this->originalData[$key] ?? null,
                'after' => $value,
            ];
        }

        // Log permission changes
        $originalPermissions = $this->record->getOriginal('permissions') ?? [];
        $newPermissions = $this->record->permissions->pluck('id')->toArray();
        
        if ($originalPermissions !== $newPermissions) {
            $changes['permissions'] = [
                'before' => $originalPermissions,
                'after' => $newPermissions,
            ];
        }

        if (!empty($changes)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'model_type' => static::getModel(),
                'model_id' => $this->record->id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage roles') || auth()->user()?->hasRole('Administrator');
    }
}

<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\AuditLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => 
                    auth()->user()?->can('manage users') || 
                    auth()->user()?->hasRole('Administrator')
                )
                ->disabled(fn () => $this->record->id === auth()->id()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
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
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }
}

<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\AuditLog;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

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
                'after' => $this->record->toArray(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage users') || auth()->user()?->hasRole('Administrator');
    }
}

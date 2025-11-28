<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use App\Models\AuditLog;
use App\Models\Room;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set organization from current user if not set
        if (!isset($data['organization_id']) && auth()->user()?->organization_id) {
            $data['organization_id'] = auth()->user()->organization_id;
        }

        // Set created_by
        $data['created_by'] = auth()->id();

        // Set current_plant_count to initial if not set
        if (!isset($data['current_plant_count']) && isset($data['initial_plant_count'])) {
            $data['current_plant_count'] = $data['initial_plant_count'];
        }

        // Validate room capacity
        if (isset($data['room_id']) && isset($data['initial_plant_count'])) {
            $room = Room::find($data['room_id']);
            if ($room) {
                $availableCapacity = $room->available_capacity;
                if ($data['initial_plant_count'] > $availableCapacity) {
                    throw new \Exception("Room capacity exceeded. Available capacity: {$availableCapacity}");
                }
            }
        }

        // Calculate expected yield if strain and plant count are set
        if (isset($data['strain_id']) && isset($data['initial_plant_count'])) {
            $strain = \App\Models\Strain::find($data['strain_id']);
            if ($strain && $strain->expected_yield_per_plant) {
                $data['expected_yield'] = $strain->expected_yield_per_plant * $data['initial_plant_count'];
            }
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
}


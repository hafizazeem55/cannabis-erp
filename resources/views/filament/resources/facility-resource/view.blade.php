@php
    $rooms = $rooms ?? collect();
    $tunnels = $tunnels ?? collect();
@endphp

<div class="space-y-8">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Facility</p>
                <p class="text-xl font-semibold text-gray-900">{{ $record->name }}</p>
                <p class="text-sm text-gray-600">{{ $record->address ?: 'No address on file' }}</p>
                <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                    @if ($record->city)<span class="rounded-full bg-gray-100 px-3 py-1">{{ $record->city }}</span>@endif
                    @if ($record->state)<span class="rounded-full bg-gray-100 px-3 py-1">{{ $record->state }}</span>@endif
                    @if ($record->country)<span class="rounded-full bg-gray-100 px-3 py-1">{{ $record->country }}</span>@endif
                    @if ($record->is_active)
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 font-semibold">Active</span>
                    @else
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-700 font-semibold">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-6">
                <div class="text-right">
                    <p class="text-2xl font-semibold text-gray-900">{{ $rooms->count() }}</p>
                    <p class="text-sm text-gray-500">Rooms</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-semibold text-gray-900">{{ $tunnels->count() }}</p>
                    <p class="text-sm text-gray-500">Tunnels</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-lg font-semibold text-gray-900">Rooms</p>
                    <p class="text-sm text-gray-500">Manage all rooms for this facility.</p>
                </div>
                <a
                    href="{{ \App\Filament\Resources\RoomResource::getUrl('create', ['facility_id' => $record->id]) }}"
                    class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:bg-primary-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Room
                </a>
            </div>

            @if ($rooms->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-sm text-gray-600">
                    No rooms have been added yet. Create one to start assigning batches.
                </div>
            @else
                <div class="mt-4 divide-y divide-gray-100 border border-gray-100 rounded-xl">
                    @foreach ($rooms as $room)
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $room->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $room->code ?: 'No code' }} · {{ ucfirst($room->type ?? 'room') }} · Capacity: {{ $room->capacity ?? 0 }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($room->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700">Inactive</span>
                                @endif
                                <a
                                    href="{{ \App\Filament\Resources\RoomResource::getUrl('edit', ['record' => $room]) }}"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700"
                                >
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-lg font-semibold text-gray-900">Tunnels</p>
                    <p class="text-sm text-gray-500">Manage tunnels attached to this facility.</p>
                </div>
                <a
                    href="{{ \App\Filament\Resources\TunnelResource::getUrl('create', ['facility_id' => $record->id]) }}"
                    class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:bg-primary-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Tunnel
                </a>
            </div>

            @if ($tunnels->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-sm text-gray-600">
                    No tunnels have been added yet. Create one to track throughput.
                </div>
            @else
                <div class="mt-4 divide-y divide-gray-100 border border-gray-100 rounded-xl">
                    @foreach ($tunnels as $tunnel)
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $tunnel->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $tunnel->code ?: 'No code' }} · {{ ucfirst($tunnel->type ?? 'tunnel') }} · Capacity: {{ $tunnel->capacity ?? 0 }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($tunnel->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700">Inactive</span>
                                @endif
                                <a
                                    href="{{ \App\Filament\Resources\TunnelResource::getUrl('edit', ['record' => $tunnel]) }}"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700"
                                >
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

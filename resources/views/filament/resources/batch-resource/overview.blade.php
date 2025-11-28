@php
    $progress = max(0, min(100, (float) ($record->progress_percentage ?? 0)));
    $isActive = (bool) $record->is_active;
    $plantCount = number_format($record->current_plant_count ?? $record->initial_plant_count ?? 0);
    $startDate = optional($record->planting_date)->format('M d, Y') ?? '--';
    $expectedHarvest = optional($record->expected_harvest_date)->format('M d, Y') ?? '--';
    $lastUpdated = optional($record->updated_at)->format('M d, Y h:i A') ?? '--';
    $batchTitle = $record->batch_code ?? 'Batch';
@endphp

<div class="space-y-6">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
            <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-1.5 font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
                Back
            </a>

            <div class="flex flex-wrap items-center gap-1 text-gray-400">
                <span>Growth Cycles</span>
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3" />
                <span>{{ $record->growthCycle?->name ?? '--' }}</span>
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3" />
                <span class="text-gray-600">{{ $batchTitle }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-3">
                <h1 class="text-3xl font-semibold text-gray-900">{{ $batchTitle }}</h1>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                        {{ $isActive ? 'active' : 'inactive' }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 capitalize text-amber-700">
                        {{ $statusLabel }}
                    </span>

                    @if ($record->room?->name)
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-gray-600">
                            <x-filament::icon icon="heroicon-o-map-pin" class="h-4 w-4" />
                            {{ $record->room->name }}
                        </span>
                    @endif

                    @if ($record->strain?->name)
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-gray-600">
                            <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                            {{ $record->strain->name }}
                        </span>
                    @endif
                </div>
            </div>

            @if ($editUrl)
                <a href="{{ $editUrl }}"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="mr-2 h-4 w-4" />
                    Edit Batch
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-700">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-6 w-6" />
                </span>
                <div>
                    <p class="text-lg font-semibold text-gray-900">Manage Stages for This Batch</p>
                    <p class="text-sm text-gray-500">View, start, complete, and track stage progression for {{ $batchTitle }}.</p>
                </div>
            </div>

            @if ($canManageStage)
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-2xl bg-gray-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700"
                    wire:click="callAction('manage_stage')"
                >
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="mr-2 h-4 w-4" />
                    Manage Stages
                </button>
            @endif
        </div>

        <div class="mt-6 space-y-2">
            <div class="flex items-center justify-between text-sm font-semibold text-gray-600">
                <span>Overall Progress</span>
                <span>{{ number_format($progress, 0) }}%</span>
            </div>
            <div class="h-3 w-full rounded-full bg-gray-100">
                <div class="h-3 rounded-full bg-primary-500 transition-all" style="width: {{ $progress }}%;"></div>
            </div>
        </div>
    </div>

    <div class="flex gap-6 flex-col lg:flex-row">
    <!-- Batch Information - Left Side -->
    <div class="flex-1 rounded-2xl border border-gray-200 bg-white p-6">
        <div class="flex items-center gap-2 text-lg font-semibold text-gray-900">
            <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-primary-500" />
            <span>Batch Information</span>
        </div>

        <dl class="mt-6 grid gap-4 text-sm text-gray-500 sm:grid-cols-2">
            <div class="space-y-1">
                <dt>Growth Cycle</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $record->growthCycle?->name ?? '--' }}</dd>
            </div>
            <div class="space-y-1">
                <dt>Strain</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $record->strain?->name ?? '--' }}</dd>
            </div>
            <div class="space-y-1">
                <dt>Room</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $record->room?->name ?? '--' }}</dd>
            </div>
            <div class="space-y-1">
                <dt>Plant Count</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $plantCount }}</dd>
            </div>
            <div class="space-y-1">
                <dt>Start Date</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $startDate }}</dd>
            </div>
            <div class="space-y-1">
                <dt>Expected Harvest</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $expectedHarvest }}</dd>
            </div>
        </dl>
    </div>

    <!-- Current Status - Right Side -->
    <div class="flex-1 rounded-2xl border border-gray-200 bg-white p-6">
        <p class="text-lg font-semibold text-gray-900">Current Status</p>
        <p class="text-sm text-gray-500">Current stage and key timestamps.</p>

        <dl class="mt-6 space-y-4 text-sm text-gray-500">
            <div class="space-y-2">
                <dt>Current Stage</dt>
                <dd>
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                        {{ $statusLabel }}
                    </span>
                </dd>
            </div>
            <div class="space-y-2">
                <dt>Status</dt>
                <dd>
                    <span class="inline-flex rounded-full {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        {{ $isActive ? 'active' : 'inactive' }}
                    </span>
                </dd>
            </div>
            <div class="space-y-1">
                <dt>Progress</dt>
                <dd class="text-base font-semibold text-gray-900">{{ number_format($progress, 0) }}%</dd>
            </div>
            <div class="space-y-1">
                <dt>Last Updated</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $lastUpdated }}</dd>
            </div>
        </dl>
    </div>
</div>
</div>

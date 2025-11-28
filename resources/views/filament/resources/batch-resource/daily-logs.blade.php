@php
    use App\Filament\Resources\BatchLogResource;
    use App\Models\Batch;

    $stageLabels = collect(Batch::STAGE_FLOW)
        ->mapWithKeys(fn (array $config, string $key) => [$key => $config['label']])
        ->toArray();
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-sm">
        <div>
            <p class="text-lg font-semibold text-gray-900">Daily Cultivation Logs</p>
            <p class="text-sm text-gray-500">
                Latest observations, environmental readings, and activities for this batch.
                @if ($logs->isNotEmpty())
                    <span class="font-semibold text-gray-700">Last entry {{ optional($logs->first()->log_date)->diffForHumans() ?? 'recently' }}.</span>
                @endif
            </p>
        </div>
        <a
            href="{{ BatchLogResource::getUrl('create', ['batch_id' => $record->id]) }}"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700"
        >
            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
            Log Today
        </a>
    </div>

    @if ($logs->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
            No daily logs recorded yet. Use <span class="font-semibold text-primary-600">Log Today</span> to capture your first entry.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($logs as $log)
                <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-primary-200">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-3 py-1 text-primary-600">
                                    <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                                    {{ optional($log->log_date)->format('M d, Y') ?? 'No Date' }}
                                </span>
                                @if ($log->stage)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-gray-700">
                                        <x-filament::icon icon="heroicon-o-adjustments-vertical" class="h-4 w-4" />
                                        {{ $stageLabels[$log->stage] ?? ucfirst($log->stage) }}
                                    </span>
                                @endif
                                @if ($log->room)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-gray-700">
                                        <x-filament::icon icon="heroicon-o-map-pin" class="h-4 w-4" />
                                        {{ $log->room->name }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $log->notes ?: 'No notes provided.' }}
                            </p>
                            @if (! empty($log->activities))
                                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Activities</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($log->activities as $activity)
                                            @php
                                                $activityLabel = is_array($activity)
                                                    ? collect([
                                                        $activity['activity'] ?? null,
                                                        $activity['details'] ?? null,
                                                        $activity['time'] ?? null,
                                                    ])
                                                        ->filter(fn ($value) => filled($value))
                                                        ->implode(' &bull; ')
                                                    : $activity;
                                            @endphp
                                            @continue(blank($activityLabel))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">
                                                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4 text-success-500" />
                                                {{ $activityLabel }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="w-full max-w-sm rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Environment & Plants</p>
                            <dl class="mt-3 grid grid-cols-2 gap-4 text-sm text-gray-600">
                                @if (! is_null($log->temperature_avg))
                                    <div>
                                        <dt class="font-semibold text-gray-900">Temperature</dt>
                                        <dd>{{ $log->temperature_avg }} °C avg</dd>
                                    </div>
                                @endif
                                @if (! is_null($log->humidity_avg))
                                    <div>
                                        <dt class="font-semibold text-gray-900">Humidity</dt>
                                        <dd>{{ $log->humidity_avg }} % avg</dd>
                                    </div>
                                @endif
                                @if (! is_null($log->plant_count))
                                    <div>
                                        <dt class="font-semibold text-gray-900">Plant Count</dt>
                                        <dd>{{ $log->plant_count }}</dd>
                                    </div>
                                @endif
                                @if (! is_null($log->mortality_count))
                                    <div>
                                        <dt class="font-semibold text-gray-900">Mortality</dt>
                                        <dd>{{ $log->mortality_count }}</dd>
                                    </div>
                                @endif
                            </dl>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                <span>Logged by {{ optional($log->loggedBy)->name ?? 'Unknown' }}</span>
                                <a href="{{ BatchLogResource::getUrl('edit', ['record' => $log->id]) }}" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700">
                                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>




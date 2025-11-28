@php
    $stateBadges = [
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'current' => 'bg-primary-50 text-primary-700 border-primary-200',
        'upcoming' => 'bg-gray-50 text-gray-500 border-gray-200',
    ];
    $logCounts = $logCounts ?? [];
    $logsByStage = $logsByStage ?? [];
    $stageLabels = collect($stages)->mapWithKeys(fn ($stage) => [$stage['key'] => $stage['label']]);
@endphp

<div
    class="space-y-8"
    x-data="{
        showModal: false,
        selectedStage: null,
        logsByStage: @js($logsByStage),
        stageLabels: @js($stageLabels),
        openStage(stage) {
            this.selectedStage = stage;
            this.showModal = true;
            document.body.classList.add('overflow-hidden');
        },
        closeStageModal() {
            this.showModal = false;
            this.selectedStage = null;
            document.body.classList.remove('overflow-hidden');
        },
        get selectedLogs() {
            return this.logsByStage?.[this.selectedStage] ?? [];
        },
        get selectedStageLabel() {
            return this.stageLabels?.[this.selectedStage] ?? this.selectedStage;
        },
    }"
    @keydown.escape.window="closeStageModal()"
>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-lg font-semibold text-gray-900">Growth Stage Progression</p>
                <p class="text-sm text-gray-500">Track every milestone from clones to packaged product.</p>
            </div>
            <div class="flex items-center gap-6 text-sm text-gray-500">
                <div class="text-right">
                    <p class="text-xl font-semibold text-gray-900">{{ number_format($record->progress_percentage ?? 0, 1) }}%</p>
                    <p>Overall Progress</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-semibold text-gray-900">{{ $record->current_plant_count }} / {{ $record->initial_plant_count }}</p>
                    <p>Current / Initial Plants</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stages as $stage)
                <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-gray-50/80 p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-base font-semibold text-gray-900 shadow-sm">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $stage['label'] }}</p>
                            <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide {{ $stateBadges[$stage['state']] ?? $stateBadges['upcoming'] }}">
                                {{ $stage['state'] === 'current' ? 'in progress' : $stage['state'] }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-gray-600">
                        @if ($stage['date'])
                            {{ $stage['date']->format('M d, Y') }}
                        @else
                            Date pending
                        @endif
                    </div>
                    <button
                        type="button"
                        class="group inline-flex items-center gap-2 text-xs font-semibold text-primary-600 transition hover:text-primary-700"
                        @click="openStage('{{ $stage['key'] }}')"
                    >
                        <span class="rounded-lg bg-primary-50 px-2.5 py-1 text-[11px] font-semibold text-primary-700 transition group-hover:bg-primary-100">
                            {{ $logCounts[$stage['key']] ?? 0 }} daily log{{ ($logCounts[$stage['key']] ?? 0) === 1 ? '' : 's' }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wide text-primary-500 transition group-hover:text-primary-700">View</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-lg font-semibold text-gray-900">Stage History</p>
                <p class="text-sm text-gray-500">Every approval and reason logged for this batch.</p>
            </div>
            <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                {{ $history->count() }} transition{{ $history->count() === 1 ? '' : 's' }}
            </span>
        </div>

        @if ($history->isEmpty())
            <div class="mt-6 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500">
                No stage transitions recorded yet. Use <span class="font-semibold text-primary-600">Manage Stage</span> to update progression.
            </div>
        @else
            <ul class="mt-6 space-y-4">
                @foreach ($history as $event)
                    <li class="rounded-2xl border border-gray-100 p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-900">
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ ucfirst($event->from_stage ?? 'start') }}
                                </span>
                                <span class="text-gray-400">&rarr;</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                    {{ ucfirst($event->to_stage) }}
                                </span>
                            </div>
                            <div class="text-right text-xs font-semibold text-gray-500">
                                <p>{{ optional($event->transition_date)->format('M d, Y g:i A') ?? 'Pending date' }}</p>
                                <button
                                    type="button"
                                    class="text-primary-600 transition hover:text-primary-700"
                                    @click="openStage('{{ $event->to_stage }}')"
                                >
                                    {{ $logCounts[$event->to_stage] ?? 0 }} daily log{{ ($logCounts[$event->to_stage] ?? 0) === 1 ? '' : 's' }}
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-gray-500">
                            @if ($event->approvedBy)
                                <p class="font-semibold text-gray-700">Approved by {{ $event->approvedBy->name }}</p>
                            @endif
                            @if ($event->reason)
                                <p class="mt-1 text-gray-600"><span class="font-semibold text-gray-800">Reason:</span> {{ $event->reason }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div
        x-cloak
        x-show="showModal"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        aria-modal="true"
        role="dialog"
    >
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="closeStageModal()"></div>
        <div
            x-show="showModal"
            x-transition
            class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200"
        >
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-400">Daily Logs</p>
                    <p class="text-lg font-semibold text-gray-900" x-text="selectedStageLabel"></p>
                    <p class="text-xs text-gray-500" x-text="`${selectedLogs.length} log${selectedLogs.length === 1 ? '' : 's'}`"></p>
                </div>
                <button
                    type="button"
                    class="rounded-full bg-gray-100 p-2 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700"
                    @click="closeStageModal()"
                >
                    <span class="sr-only">Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[70vh] space-y-3 overflow-y-auto px-6 py-4">
                <template x-if="selectedLogs.length === 0">
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        No daily logs recorded for this stage yet.
                    </div>
                </template>

                <template x-for="log in selectedLogs" :key="log.id">
                    <div class="rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="log.date || 'Date not set'"></p>
                                <p class="text-xs text-gray-500" x-text="log.notes || 'No notes added'"></p>
                            </div>
                            <a
                                :href="log.edit_url"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 transition hover:bg-primary-100"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487 19.5 7.125m-5.863-2.638 2.638 2.638M4.5 20.25h15m-7.5-3.75h-.008v.008H12V16.5Z" />
                                </svg>
                                Edit log
                            </a>
                        </div>

                        <div class="mt-3 grid gap-2 text-xs text-gray-600 sm:grid-cols-2">
                            <div>
                                <span class="font-semibold text-gray-800">Activities:</span>
                                <span x-text="(log.activities?.length ? log.activities.join(', ') : 'Not recorded')"></span>
                            </div>
                            <div x-show="log.room || log.tunnel">
                                <span class="font-semibold text-gray-800">Location:</span>
                                <span x-text="[log.room, log.tunnel].filter(Boolean).join(' · ')"></span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-800">Plants:</span>
                                <span x-text="log.plant_count ?? '—'"></span>
                                <span class="text-gray-400"> / Mortality: </span>
                                <span x-text="log.mortality_count ?? 0"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

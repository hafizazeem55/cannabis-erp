@php
    $logCounts = $logCounts ?? [];
@endphp

<div class="space-y-4">
    @forelse ($stages as $stage)
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-900">
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                        {{ ucfirst($stage->from_stage ?? 'start') }}
                    </span>
                    <span class="text-gray-400">&rarr;</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                        {{ ucfirst($stage->to_stage) }}
                    </span>
                </div>
                <div class="text-right text-xs font-semibold text-gray-500">
                    <p>{{ optional($stage->transition_date)->format('M d, Y g:i A') ?? 'Pending date' }}</p>
                    <p class="text-gray-400">{{ $logCounts[$stage->to_stage] ?? 0 }} daily log{{ ($logCounts[$stage->to_stage] ?? 0) === 1 ? '' : 's' }}</p>
                </div>
            </div>

            <div class="mt-3 space-y-2 text-sm text-gray-600">
                @if ($stage->approvedBy)
                    <p class="font-semibold text-gray-700">Approved by {{ $stage->approvedBy->name }}</p>
                @endif

                @if ($stage->reason)
                    <p><span class="font-semibold text-gray-900">Reason:</span> {{ $stage->reason }}</p>
                @endif

                @if ($stage->notes)
                    <p><span class="font-semibold text-gray-900">Notes:</span> {{ $stage->notes }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-8 text-center text-sm text-gray-500">
            No stage transitions recorded for this batch.
        </div>
    @endforelse
</div>

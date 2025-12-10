@php
    /** @var \App\Models\GrowthCycle $growthCycle */
    $batches = $growthCycle->batches()
        ->with(['strain', 'room'])
        ->orderByDesc('created_at')
        ->get();
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Assigned Batches
            </h3>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Track every cultivation batch tied to this growth cycle.
            </p>
        </div>

        <x-filament::button
            tag="a"
            color="primary"
            size="sm"
            icon="heroicon-o-plus-circle"
            :href="\App\Filament\Resources\BatchResource::getUrl('create', ['growth_cycle_id' => $growthCycle->getKey()])"
        >
            Add Batch
        </x-filament::button>
    </div>

    @if ($batches->isEmpty())
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                No batches assigned to this growth cycle yet. Use the "Add Batch" button to create or link batches.
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Batch
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Strain
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Room
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Expected Harvest
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900/20">
                        @foreach ($batches as $batch)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $batch->batch_code }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $batch->strain?->name ?? 'Not set' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $batch->room?->name ?? 'Unassigned' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    <x-filament::badge
                                        :color="match ($batch->status) {
                                            'cloning', 'clone', 'propagation' => 'gray',
                                            'vegetative' => 'info',
                                            'flowering', 'flower' => 'warning',
                                            'harvest', 'drying', 'curing', 'packaging', 'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary',
                                        }"
                                    >
                                        {{ ucfirst($batch->status) }}
                                    </x-filament::badge>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ optional($batch->expected_harvest_date)->format(config('filament.date_format', 'M d, Y')) ?? 'Not scheduled' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex justify-end gap-2">
                                        <x-filament::button
                                            tag="a"
                                            size="sm"
                                            color="gray"
                                            icon="heroicon-o-pencil-square"
                                            :href="\App\Filament\Resources\BatchResource::getUrl('edit', ['record' => $batch->getKey()])"
                                        >
                                            Manage
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</div>

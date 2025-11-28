<x-filament-panels::page>
    <div class="space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-gray-900">Environmental Monitoring</h1>
            <p class="text-sm text-gray-600">Monitor and control environmental conditions across all grow rooms and tunnels.</p>
        </div>

        <div class="bg-white border rounded-lg shadow-sm">
            <div class="grid grid-cols-3 gap-2 p-2 text-sm font-semibold text-gray-600">
                <button
                    wire:click="$set('activePrimaryTab','live')"
                    class="flex items-center justify-center gap-2 rounded-md px-3 py-2 transition {{ $activePrimaryTab === 'live' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'hover:bg-gray-50' }}"
                >
                    <x-heroicon-o-bolt class="w-4 h-4" />
                    Live Monitoring
                </button>
                <button
                    wire:click="$set('activePrimaryTab','historical')"
                    class="flex items-center justify-center gap-2 rounded-md px-3 py-2 transition {{ $activePrimaryTab === 'historical' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'hover:bg-gray-50' }}"
                >
                    <x-heroicon-o-chart-bar class="w-4 h-4" />
                    Historical Trends
                </button>
                <button
                    wire:click="$set('activePrimaryTab','thresholds')"
                    class="flex items-center justify-center gap-2 rounded-md px-3 py-2 transition {{ $activePrimaryTab === 'thresholds' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'hover:bg-gray-50' }}"
                >
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                    Thresholds
                </button>
            </div>
        </div>

        @if(empty($spaces))
            <div class="bg-white border rounded-lg shadow-sm p-6 text-sm text-gray-700">
                No rooms or tunnels found. Add a room or tunnel to start monitoring.
            </div>
        @elseif($activePrimaryTab === 'live')
            <div class="bg-white border rounded-lg shadow-sm">
                <div class="flex flex-wrap gap-2 p-2 text-sm font-semibold text-gray-600">
                    <button
                        wire:click="$set('activeLiveTab','overview')"
                        class="rounded-md px-3 py-2 transition {{ $activeLiveTab === 'overview' ? 'bg-gray-900 text-white' : 'hover:bg-gray-100' }}"
                    >
                        Overview
                    </button>
                    @foreach($spaces as $space)
                        <button
                            wire:click="$set('activeLiveTab','{{ $space['slug'] }}')"
                            class="rounded-md px-3 py-2 transition {{ $activeLiveTab === $space['slug'] ? 'bg-gray-900 text-white' : 'hover:bg-gray-100' }}"
                        >
                            {{ $space['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if($activeLiveTab === 'overview')
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($liveCards as $card)
                        <div class="bg-white border rounded-lg shadow-sm p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs text-gray-500">Updated {{ $card['recorded_at'] ? $card['recorded_at']->diffForHumans() : 'No data yet' }}</p>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $card['space']['label'] }}</h3>
                                    @if($card['space']['facility'])
                                        <p class="text-xs text-gray-500">{{ $card['space']['facility'] }}</p>
                                    @endif
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ str_contains($card['space']['slug'],'room:') ? 'Room' : 'Tunnel' }}</span>
                            </div>

                            <div class="space-y-2 text-sm text-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-fire class="w-4 h-4 text-gray-500" />
                                        <span>Temperature</span>
                                    </div>
                                    <span class="font-semibold text-emerald-600">{{ $card['temperature'] !== null ? number_format((float) $card['temperature'], 1) . ' °C' : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-adjustments-vertical class="w-4 h-4 text-gray-500" />
                                        <span>Humidity</span>
                                    </div>
                                    <span class="font-semibold text-emerald-600">{{ $card['humidity'] !== null ? number_format((float) $card['humidity'], 1) . '%' : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-cloud class="w-4 h-4 text-gray-500" />
                                        <span>CO₂</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $card['co2'] !== null ? number_format((float) $card['co2'], 0) . ' ppm' : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-beaker class="w-4 h-4 text-gray-500" />
                                        <span>pH Level</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $card['ph'] !== null ? number_format((float) $card['ph'], 1) : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @php
                    $focused = collect($liveCards)->firstWhere('space.slug', $activeLiveTab);
                    $space = $focused['space'] ?? null;
                @endphp
                <div class="space-y-4">
                    @if($space)
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="bg-white border rounded-lg p-4">
                                <p class="text-xs text-gray-500 flex items-center gap-2"><x-heroicon-o-fire class="w-4 h-4" /> Temperature</p>
                                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $focused['temperature'] !== null ? number_format((float) $focused['temperature'], 1) . ' °C' : 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Target: {{ $space['thresholds']['temperature_min'] ?? 'N/A' }} - {{ $space['thresholds']['temperature_max'] ?? 'N/A' }} °C</p>
                            </div>
                            <div class="bg-white border rounded-lg p-4">
                                <p class="text-xs text-gray-500 flex items-center gap-2"><x-heroicon-o-adjustments-vertical class="w-4 h-4" /> Humidity</p>
                                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $focused['humidity'] !== null ? number_format((float) $focused['humidity'], 1) . '%' : 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Target: {{ $space['thresholds']['humidity_min'] ?? 'N/A' }} - {{ $space['thresholds']['humidity_max'] ?? 'N/A' }}%</p>
                            </div>
                            <div class="bg-white border rounded-lg p-4">
                                <p class="text-xs text-gray-500 flex items-center gap-2"><x-heroicon-o-cloud class="w-4 h-4" /> CO₂ Level</p>
                                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $focused['co2'] !== null ? number_format((float) $focused['co2'], 0) : 'N/A' }} <span class="text-sm font-medium text-gray-600">ppm</span></p>
                                <p class="text-xs text-gray-500">Target: {{ $space['thresholds']['co2_min'] ?? 'N/A' }} - {{ $space['thresholds']['co2_max'] ?? 'N/A' }} ppm</p>
                            </div>
                            <div class="bg-white border rounded-lg p-4">
                                <p class="text-xs text-gray-500 flex items-center gap-2"><x-heroicon-o-beaker class="w-4 h-4" /> pH Level</p>
                                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $focused['ph'] !== null ? number_format((float) $focused['ph'], 1) : 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Optimal: {{ $space['thresholds']['ph_min'] ?? 'N/A' }} - {{ $space['thresholds']['ph_max'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="bg-white border rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-900">Environmental Status</p>
                            <p class="text-xs text-gray-500">Last updated {{ $focused['recorded_at'] ? $focused['recorded_at']->diffForHumans() : 'No data yet' }}</p>
                            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <x-heroicon-o-check-circle class="w-4 h-4" />
                                {{ $focused['recorded_at'] ? 'All Systems Normal' : 'Awaiting Data' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-white border rounded-lg p-6 text-center text-gray-600">Select a room or tunnel to view details.</div>
                    @endif
                </div>
            @endif
        @endif

        @if($activePrimaryTab === 'historical')
            <div class="bg-white border rounded-lg shadow-sm">
                <div class="flex flex-col gap-4 p-4">
                    <div class="flex flex-wrap gap-3 justify-end">
                        <select wire:model.live="trendSpace" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400">
                            @foreach($spaces as $space)
                                <option value="{{ $space['slug'] }}">{{ $space['label'] }} {{ $space['facility'] ? ' - ' . $space['facility'] : '' }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="trendParameter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400">
                            <option value="temperature">Temperature</option>
                            <option value="humidity">Humidity</option>
                            <option value="co2">CO₂</option>
                            <option value="ph">pH</option>
                            <option value="ec">EC</option>
                        </select>
                        <select wire:model.live="trendDays" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-amber-400 focus:ring-amber-400">
                            <option value="7">7 Days</option>
                            <option value="30">30 Days</option>
                            <option value="90">90 Days</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-lg font-semibold text-gray-900">Environmental Trends</h3>
                        <div class="bg-gray-50 border rounded-lg p-4">
                            <div wire:ignore>
                                <canvas id="trendChart" class="w-full h-72"></canvas>
                            </div>
                            @if(empty($trendSeries))
                                <p class="text-sm text-gray-500 mt-3">No readings captured for this selection yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($activePrimaryTab === 'thresholds')
            <div class="bg-white border rounded-lg shadow-sm p-6">
                <p class="text-sm text-gray-700">Threshold management will be added here next. We will let you define alert ranges for rooms and tunnels and trigger notifications when readings exceed them.</p>
            </div>
        @endif
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <script>
        document.addEventListener('livewire:init', () => {
            const ctx = document.getElementById('trendChart')?.getContext('2d');
            if (!ctx) return;

            const buildConfig = (labels, data, label) => ({
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        tension: 0.25,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { grid: { color: '#e5e7eb' } },
                    },
                },
            });

            let chart = new Chart(ctx, buildConfig(@js($trendLabels), @js($trendSeries), 'Reading'));

            const updateChart = (labels, data, label) => {
                chart.data.labels = labels;
                chart.data.datasets[0].data = data;
                chart.data.datasets[0].label = label;
                chart.update();
            };

            Livewire.on('trend-data-updated', (payload) => {
                const labelMap = {
                    temperature: 'Temperature (°C)',
                    humidity: 'Humidity (%)',
                    co2: 'CO₂ (ppm)',
                    ph: 'pH',
                    ec: 'EC (mS/cm)',
                };
                updateChart(payload.labels, payload.series, labelMap[payload.parameter] ?? 'Reading');
            });
        });
    </script>
</x-filament-panels::page>

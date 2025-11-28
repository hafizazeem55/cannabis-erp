<?php

namespace App\Filament\Pages;

use App\Models\BatchLog;
use App\Models\Room;
use App\Models\Tunnel;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class EnvironmentalMonitoring extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Environmental Monitoring';
    protected static ?string $navigationGroup = 'ERP Cultivation';
    protected static ?int $navigationSort = 7;
    protected static string $view = 'filament.pages.environmental-monitoring';

    public array $spaces = [];
    public array $liveCards = [];
    public string $activePrimaryTab = 'live';
    public string $activeLiveTab = 'overview';
    public ?string $trendSpace = null;
    public string $trendParameter = 'temperature';
    public int $trendDays = 30;
    public array $trendLabels = [];
    public array $trendSeries = [];

    public function mount(): void
    {
        $this->loadSpaces();
        $this->loadLiveCards();
        $this->setDefaultTrendSpaceIfNeeded();
        $this->loadTrendData();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('manage cultivation') || auth()->user()?->can('view cultivation') || auth()->user()?->hasRole('Administrator');
    }

    protected function loadSpaces(): void
    {
        $rooms = Room::with('facility')
            ->orderBy('name')
            ->get()
            ->map(fn (Room $room) => [
                'slug' => 'room:' . $room->id,
                'type' => Room::class,
                'id' => $room->id,
                'name' => $room->name,
                'label' => $room->name,
                'facility' => $room->facility?->name,
                'thresholds' => [
                    'temperature_min' => $room->temperature_min,
                    'temperature_max' => $room->temperature_max,
                    'humidity_min' => $room->humidity_min,
                    'humidity_max' => $room->humidity_max,
                    'co2_min' => $room->co2_min,
                    'co2_max' => $room->co2_max,
                    'ph_min' => $room->ph_min,
                    'ph_max' => $room->ph_max,
                    'ec_min' => $room->ec_min,
                    'ec_max' => $room->ec_max,
                ],
            ]);

        $tunnels = Tunnel::with('facility')
            ->orderBy('name')
            ->get()
            ->map(fn (Tunnel $tunnel) => [
                'slug' => 'tunnel:' . $tunnel->id,
                'type' => Tunnel::class,
                'id' => $tunnel->id,
                'name' => $tunnel->name,
                'label' => $tunnel->name . (str_contains(strtolower($tunnel->name), 'tunnel') ? '' : ' (Tunnel)'),
                'facility' => $tunnel->facility?->name,
                'thresholds' => [
                    'temperature_min' => $tunnel->temperature_min,
                    'temperature_max' => $tunnel->temperature_max,
                    'humidity_min' => $tunnel->humidity_min,
                    'humidity_max' => $tunnel->humidity_max,
                    'co2_min' => $tunnel->co2_min,
                    'co2_max' => $tunnel->co2_max,
                    'ph_min' => $tunnel->ph_min,
                    'ph_max' => $tunnel->ph_max,
                    'ec_min' => $tunnel->ec_min,
                    'ec_max' => $tunnel->ec_max,
                ],
            ]);

        $this->spaces = $rooms->concat($tunnels)->values()->toArray();
    }

    protected function loadLiveCards(): void
    {
        if (empty($this->spaces)) {
            $this->liveCards = [];
            return;
        }

        $groupedIds = collect($this->spaces)
            ->groupBy('type')
            ->map(fn ($spaces) => collect($spaces)->pluck('id')->all());

        $rooms = $groupedIds[Room::class] ?? [];
        $tunnels = $groupedIds[Tunnel::class] ?? [];
        $hasTunnelColumn = Schema::hasColumn('batch_logs', 'tunnel_id');

        $logs = BatchLog::query()
            ->where(function ($query) use ($rooms, $tunnels, $hasTunnelColumn) {
                $addedCondition = false;

                if (! empty($rooms)) {
                    $query->whereIn('room_id', $rooms);
                    $addedCondition = true;
                }

                if ($hasTunnelColumn && ! empty($tunnels)) {
                    $method = $addedCondition ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('tunnel_id', $tunnels);
                }
            })
            ->orderByDesc('log_date')
            ->get()
            ->groupBy(function (BatchLog $log) {
                if ($log->room_id) {
                    return 'room:' . $log->room_id;
                }

                if ($log->tunnel_id) {
                    return 'tunnel:' . $log->tunnel_id;
                }

                return 'unknown';
            });

        $this->liveCards = collect($this->spaces)
            ->map(function (array $space) use ($logs) {
                $key = $space['slug'];
                $log = $logs->get($key)?->first();

                return [
                    'space' => $space,
                    'temperature' => $log?->temperature_avg,
                    'humidity' => $log?->humidity_avg,
                    'co2' => $log?->co2_avg,
                    'ph' => $log?->ph_avg,
                    'ec' => $log?->ec_avg,
                    'recorded_at' => $log?->log_date,
                ];
            })
            ->toArray();
    }

    protected function loadTrendData(): void
    {
        if (empty($this->spaces) || ! $this->trendSpace) {
            $this->trendLabels = [];
            $this->trendSeries = [];
            return;
        }

        [$type, $id] = explode(':', $this->trendSpace);
        $column = $this->parameterColumn();
        $days = max(1, $this->trendDays);
        $hasTunnelColumn = Schema::hasColumn('batch_logs', 'tunnel_id');

        $spaceSlugs = collect($this->spaces)->pluck('slug');
        if (! $spaceSlugs->contains($this->trendSpace) || ($type === 'tunnel' && ! $hasTunnelColumn)) {
            $this->trendLabels = [];
            $this->trendSeries = [];
            $this->dispatch('trend-data-updated', labels: $this->trendLabels, series: $this->trendSeries, parameter: $this->trendParameter)->self();
            return;
        }

        $startDate = Carbon::now()->subDays($days);

        $records = BatchLog::query()
            ->when($type === 'room', fn ($query) => $query->where('room_id', (int) $id))
            ->when($type === 'tunnel' && $hasTunnelColumn, fn ($query) => $query->where('tunnel_id', (int) $id))
            ->where('log_date', '>=', $startDate)
            ->orderBy('log_date')
            ->get(['log_date', $column]);

        // If no data for this space/parameter, try the most recent space that has data.
        if ($records->whereNotNull($column)->isEmpty()) {
            $fallbackSpace = $this->findSpaceWithData($column, $startDate, $hasTunnelColumn);
            if ($fallbackSpace && $fallbackSpace !== $this->trendSpace) {
                $this->trendSpace = $fallbackSpace;
                $this->loadTrendData();
                return;
            }
        }

        $this->trendLabels = $records->pluck('log_date')
            ->map(fn ($date) => $date?->format('M d'))
            ->toArray();

        $this->trendSeries = $records->pluck($column)
            ->map(fn ($value) => $value !== null ? (float) $value : null)
            ->toArray();

        $this->dispatch(
            'trend-data-updated',
            labels: $this->trendLabels,
            series: $this->trendSeries,
            parameter: $this->trendParameter
        )->self();
    }

    public function updatedTrendSpace(): void
    {
        $this->loadTrendData();
    }

    public function updatedTrendParameter(): void
    {
        $this->loadTrendData();
    }

    public function updatedTrendDays(): void
    {
        $this->loadTrendData();
    }

    protected function parameterColumn(): string
    {
        return match ($this->trendParameter) {
            'humidity' => 'humidity_avg',
            'co2' => 'co2_avg',
            'ph' => 'ph_avg',
            'ec' => 'ec_avg',
            default => 'temperature_avg',
        };
    }

    protected function findSpaceWithData(string $column, Carbon $startDate, bool $hasTunnelColumn): ?string
    {
        $spaceSlugs = collect($this->spaces)->pluck('slug');

        $log = BatchLog::query()
            ->whereNotNull($column)
            ->where('log_date', '>=', $startDate)
            ->orderByDesc('log_date')
            ->first();

        if ($log?->room_id && $spaceSlugs->contains('room:' . $log->room_id)) {
            return 'room:' . $log->room_id;
        }

        if ($hasTunnelColumn && $log?->tunnel_id && $spaceSlugs->contains('tunnel:' . $log->tunnel_id)) {
            return 'tunnel:' . $log->tunnel_id;
        }

        return null;
    }

    protected function setDefaultTrendSpaceIfNeeded(): void
    {
        $spaceSlugs = collect($this->spaces)->pluck('slug');

        if ($this->trendSpace && $spaceSlugs->contains($this->trendSpace)) {
            return;
        }

        $hasTunnelColumn = Schema::hasColumn('batch_logs', 'tunnel_id');
        $latestLog = BatchLog::query()
            ->select(['room_id', 'tunnel_id', 'log_date'])
            ->orderByDesc('log_date')
            ->first();

        if ($latestLog?->room_id && $spaceSlugs->contains('room:' . $latestLog->room_id)) {
            $this->trendSpace = 'room:' . $latestLog->room_id;
            return;
        }

        if ($hasTunnelColumn && $latestLog?->tunnel_id && $spaceSlugs->contains('tunnel:' . $latestLog->tunnel_id)) {
            $this->trendSpace = 'tunnel:' . $latestLog->tunnel_id;
            return;
        }

        $this->trendSpace = $spaceSlugs->first() ?: null;
    }
}

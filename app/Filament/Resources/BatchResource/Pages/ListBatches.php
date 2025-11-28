<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use App\Filament\Resources\BatchResource\Widgets\BatchSummaryStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBatches extends ListRecords
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->can('manage cultivation') || auth()->user()?->hasRole('Administrator')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchSummaryStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Batches'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)
                    ->whereNotIn('status', ['completed', 'cancelled'])),
            'clone' => Tab::make('Clone/Propagation')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['clone', 'propagation'])),
            'vegetative' => Tab::make('Vegetative')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'vegetative')),
            'flower' => Tab::make('Flower')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'flower')),
            'harvest' => Tab::make('Harvest')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'harvest')),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
        ];
    }
}


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
            'cloning' => Tab::make('Cloning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['cloning', 'clone', 'propagation'])),
            'vegetative' => Tab::make('Vegetative')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'vegetative')),
            'flowering' => Tab::make('Flowering')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['flowering', 'flower'])),
            'harvest' => Tab::make('Harvest')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'harvest')),
            'drying' => Tab::make('Drying')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'drying')),
            'curing' => Tab::make('Curing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'curing')),
            'packaging' => Tab::make('Packaging')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'packaging')),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
        ];
    }
}


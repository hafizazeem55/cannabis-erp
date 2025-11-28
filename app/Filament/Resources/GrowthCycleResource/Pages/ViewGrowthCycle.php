<?php

namespace App\Filament\Resources\GrowthCycleResource\Pages;

use App\Filament\Resources\BatchResource;
use App\Filament\Resources\GrowthCycleResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewGrowthCycle extends ViewRecord
{
    protected static string $resource = GrowthCycleResource::class;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_batch')
                ->label('Add Batch')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->url(fn () => BatchResource::getUrl('create', ['growth_cycle_id' => $this->record?->getKey()])),
            Actions\EditAction::make()
                ->label('Edit Cycle'),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('Growth Cycle View')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Overview')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    Section::make('Cycle Summary')
                                        ->schema([
                                            TextEntry::make('facility.name')
                                                ->label('Facility'),
                                            TextEntry::make('status')
                                                ->label('Status')
                                                ->badge()
                                                ->color(fn (?string $state) => match ($state) {
                                                    'planning' => 'warning',
                                                    'active', 'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'gray',
                                                })
                                                ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '')),
                                            TextEntry::make('start_date')
                                                ->label('Start Date')
                                                ->date(),
                                            TextEntry::make('expected_end_date')
                                                ->label('Expected End Date')
                                                ->date()
                                                ->placeholder('Not scheduled'),
                                            TextEntry::make('actual_end_date')
                                                ->label('Actual End Date')
                                                ->date()
                                                ->placeholder('Not set'),
                                        ])
                                        ->columns(3)
                                        ->columnSpan(8),

                                    Section::make('Strains')
                                        ->schema([
                                            TextEntry::make('primaryStrain.name')
                                                ->label('Primary Strain')
                                                ->badge()
                                                ->color('primary')
                                                ->placeholder('Not set'),
                                            TextEntry::make('strains_list')
                                                ->label('All Strains')
                                                ->state(fn ($record) => $record->strains->isNotEmpty()
                                                    ? $record->strains->pluck('name')->implode(', ')
                                                    : 'No strains linked'
                                                )
                                                ->badge()
                                                ->color('primary'),
                                        ])
                                        ->columns(1)
                                        ->columnSpan(4),
                                ])
                                ->columnSpanFull(),

                            Section::make('Notes')
                                ->schema([
                                    TextEntry::make('notes')
                                        ->label('Notes')
                                        ->markdown()
                                        ->placeholder('No notes added yet.'),
                                ])
                                ->columnSpanFull(),

                            Section::make('Upcoming Modules')
                                ->schema([
                                    TextEntry::make('compliance_placeholder')
                                        ->state('Compliance record tracking for growth cycles is coming soon.')
                                        ->markdown(),
                                    TextEntry::make('qms_placeholder')
                                        ->state('Link QMS records to this growth cycle to monitor SOPs and audits.')
                                        ->markdown(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Batches')
                        ->schema([
                            ViewEntry::make('batches_table')
                                ->view('filament.resources.growth-cycle.tabs.batches')
                                ->viewData(fn ($record) => [
                                    'growthCycle' => $record,
                                ]),
                        ]),
                ]),
        ]);
    }
}

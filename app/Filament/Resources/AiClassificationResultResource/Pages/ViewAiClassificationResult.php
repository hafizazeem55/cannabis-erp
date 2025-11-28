<?php

namespace App\Filament\Resources\AiClassificationResultResource\Pages;

use App\Filament\Resources\AiClassificationResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewAiClassificationResult extends ViewRecord
{
    protected static string $resource = AiClassificationResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Plant Image')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('')
                            ->disk('public')
                            ->height(400),
                    ]),

                Infolists\Components\Section::make('Classification Results')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('growth_stage')
                                    ->label('Growth Stage')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'seedling' => 'info',
                                        'vegetative' => 'success',
                                        'pre_flowering' => 'warning',
                                        'flowering' => 'primary',
                                        'late_flowering' => 'danger',
                                        'harvest_ready' => 'gray',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('growth_stage_confidence')
                                    ->label('Confidence')
                                    ->suffix('%')
                                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),

                                Infolists\Components\TextEntry::make('health_status')
                                    ->label('Health Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'healthy' => 'success',
                                        'stressed' => 'warning',
                                        'diseased' => 'danger',
                                        'dying' => 'danger',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('health_status_confidence')
                                    ->label('Confidence')
                                    ->suffix('%')
                                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),

                                Infolists\Components\TextEntry::make('leaf_issue')
                                    ->label('Leaf Issue')
                                    ->badge()
                                    ->color('warning')
                                    ->default('None')
                                    ->formatStateUsing(fn ($state) => $state ?? 'None'),

                                Infolists\Components\TextEntry::make('leaf_issue_confidence')
                                    ->label('Confidence')
                                    ->suffix('%')
                                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                                    ->default('N/A'),

                                Infolists\Components\TextEntry::make('strain_type')
                                    ->label('Strain Type')
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('strain_type_confidence')
                                    ->label('Confidence')
                                    ->suffix('%')
                                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                            ]),
                    ]),

                Infolists\Components\Section::make('Batch Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('batch.batch_code')
                            ->label('Batch Code'),

                        Infolists\Components\TextEntry::make('batch.strain.name')
                            ->label('Strain'),

                        Infolists\Components\TextEntry::make('batch.stage')
                            ->label('Batch Stage')
                            ->badge(),

                        Infolists\Components\TextEntry::make('batch.room.name')
                            ->label('Room'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('metadata')
                            ->label('Additional Details')
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return collect($state)
                                        ->map(fn ($value, $key) => ucwords(str_replace('_', ' ', $key)) . ': ' . (is_array($value) ? json_encode($value) : $value))
                                        ->join("\n");
                                }
                                return $state ?? 'No additional details';
                            })
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Classified By'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Classification Date')
                            ->dateTime('F j, Y g:i A'),
                    ])
                    ->columns(2),
            ]);
    }
}

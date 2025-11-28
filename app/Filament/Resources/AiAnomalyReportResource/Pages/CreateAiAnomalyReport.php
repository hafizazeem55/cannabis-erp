<?php

namespace App\Filament\Resources\AiAnomalyReportResource\Pages;

use App\Filament\Resources\AiAnomalyReportResource;
use App\Services\AI\PlantAnomalyDetectionService;
use Filament\Resources\Pages\CreateRecord;

class CreateAiAnomalyReport extends CreateRecord
{
    protected static string $resource = AiAnomalyReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // If image is uploaded, run anomaly detection
        $service = app(PlantAnomalyDetectionService::class);
        
        try {
            $report = $service->detectAnomaly(
                $this->record->image_path,
                $this->record->batch_id,
                $this->record->room_id,
                auth()->id()
            );

            // Update the current record with AI results
            $this->record->update([
                'is_anomaly' => $report->is_anomaly,
                'confidence' => $report->confidence,
                'detected_issue' => $report->detected_issue,
                'issue_description' => $report->issue_description,
                'recommended_action' => $report->recommended_action,
                'severity' => $report->severity,
                'provider' => $report->provider,
                'raw_response' => $report->raw_response,
            ]);
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('AI Detection Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

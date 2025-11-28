<?php

namespace App\Filament\Resources\AiClassificationResultResource\Pages;

use App\Filament\Resources\AiClassificationResultResource;
use App\Services\AI\PlantClassificationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAiClassificationResult extends CreateRecord
{
    protected static string $resource = AiClassificationResultResource::class;

    protected function afterCreate(): void
    {
        try {
            // Get the classification service
            $classificationService = app(PlantClassificationService::class);

            // Run AI classification on the uploaded image
            $result = $classificationService->classifyPlant(
                imagePath: storage_path('app/public/' . $this->record->image_path),
                batchId: $this->record->batch_id,
                userId: $this->record->user_id
            );

            // Update the record with AI results
            $this->record->update([
                'growth_stage' => $result->growth_stage,
                'health_status' => $result->health_status,
                'leaf_issue' => $result->leaf_issue,
                'strain_type' => $result->strain_type,
                'growth_stage_confidence' => $result->growth_stage_confidence,
                'health_status_confidence' => $result->health_status_confidence,
                'leaf_issue_confidence' => $result->leaf_issue_confidence,
                'strain_type_confidence' => $result->strain_type_confidence,
                'metadata' => $result->metadata,
            ]);

            // Refresh the record
            $this->record->refresh();

            // Show success notification
            Notification::make()
                ->title('Classification Complete')
                ->body("Plant classified as {$result->growth_stage} stage with {$result->health_status} health status.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log error
            logger()->error('AI Classification failed', [
                'record_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);

            // Show error notification
            Notification::make()
                ->title('Classification Failed')
                ->body('Unable to classify plant. Please try again.')
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}

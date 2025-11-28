<?php

namespace App\Console\Commands;

use App\Models\AiEmbedding;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Strain;
use App\Models\Room;
use App\Models\EnvironmentalReading;
use App\Models\Harvest;
use App\Models\Facility;
use App\Models\GrowthCycle;
use App\Services\AI\OpenAIProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildAIKnowledgebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:build-knowledgebase 
                            {--source=* : Specific sources to build (batches, strains, etc)}
                            {--rebuild : Rebuild all embeddings}
                            {--batch-size=10 : Number of items to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build or update the AI knowledge base with embeddings from cultivation data';

    protected OpenAIProvider $provider;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->provider = app(OpenAIProvider::class);

        if (!$this->provider->isAvailable()) {
            $this->error('OpenAI provider is not available. Check your API key configuration.');
            return 1;
        }

        $this->info('🚀 Starting AI Knowledge Base Builder...');
        $this->newLine();

        $rebuild = $this->option('rebuild');
        $sources = $this->option('source') ?: array_keys(config('ai.rag.data_sources', []));
        $batchSize = (int) $this->option('batch-size');

        $totalProcessed = 0;
        $totalSkipped = 0;

        foreach ($sources as $source) {
            if (!config("ai.rag.data_sources.{$source}", false)) {
                $this->warn("⏭️  Skipping {$source} (disabled in config)");
                continue;
            }

            $this->info("📊 Processing {$source}...");
            
            $result = match($source) {
                'batches' => $this->processBatches($rebuild, $batchSize),
                'strains' => $this->processStrains($rebuild, $batchSize),
                'batch_logs' => $this->processBatchLogs($rebuild, $batchSize),
                'environmental_readings' => $this->processEnvironmentalReadings($rebuild, $batchSize),
                'harvests' => $this->processHarvests($rebuild, $batchSize),
                'rooms' => $this->processRooms($rebuild, $batchSize),
                'facilities' => $this->processFacilities($rebuild, $batchSize),
                'growth_cycles' => $this->processGrowthCycles($rebuild, $batchSize),
                default => ['processed' => 0, 'skipped' => 0],
            };

            $totalProcessed += $result['processed'];
            $totalSkipped += $result['skipped'];

            $this->info("  ✅ Processed: {$result['processed']} | ⏭️  Skipped: {$result['skipped']}");
            $this->newLine();
        }

        $this->info("🎉 Knowledge base build complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $totalProcessed],
                ['Total Skipped', $totalSkipped],
                ['Total Embeddings', AiEmbedding::count()],
            ]
        );

        return 0;
    }

    /**
     * Process batches
     */
    protected function processBatches(bool $rebuild, int $batchSize): array
    {
        $batches = Batch::with(['strain', 'room', 'growthCycle'])->get();
        $processed = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($batches->count());

        foreach ($batches as $batch) {
            $content = $this->generateBatchContent($batch);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('batches', $batch->id, $hash)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'batches',
                    $batch->id,
                    $content,
                    $embedding,
                    [
                        'batch_code' => $batch->batch_code,
                        'batch_id' => $batch->id,
                        'strain_name' => $batch->strain->name ?? null,
                        'status' => $batch->status,
                        'room_name' => $batch->room->name ?? null,
                    ]
                );
                $processed++;
            }

            $bar->advance();
            usleep(100000); // Rate limiting: 100ms delay
        }

        $bar->finish();
        $this->newLine();

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process strains
     */
    protected function processStrains(bool $rebuild, int $batchSize): array
    {
        $strains = Strain::all();
        $processed = 0;
        $skipped = 0;

        foreach ($strains as $strain) {
            $content = $this->generateStrainContent($strain);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('strains', $strain->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'strains',
                    $strain->id,
                    $content,
                    $embedding,
                    [
                        'strain_name' => $strain->name,
                        'strain_type' => $strain->type,
                    ]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process batch logs
     */
    protected function processBatchLogs(bool $rebuild, int $batchSize): array
    {
        $logs = BatchLog::with(['batch', 'room'])->get();
        $processed = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $content = $this->generateBatchLogContent($log);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('batch_logs', $log->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'batch_logs',
                    $log->id,
                    $content,
                    $embedding,
                    [
                        'batch_id' => $log->batch_id,
                        'batch_code' => $log->batch->batch_code ?? null,
                        'log_date' => $log->log_date->format('Y-m-d'),
                    ]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process environmental readings
     */
    protected function processEnvironmentalReadings(bool $rebuild, int $batchSize): array
    {
        $readings = EnvironmentalReading::with('facility')->get();
        $processed = 0;
        $skipped = 0;

        foreach ($readings as $reading) {
            $content = $this->generateEnvironmentalContent($reading);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('environmental_readings', $reading->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'environmental_readings',
                    $reading->id,
                    $content,
                    $embedding,
                    [
                        'facility_id' => $reading->facility_id,
                        'space_type' => $reading->space_type,
                        'recorded_at' => $reading->recorded_at->format('Y-m-d H:i'),
                    ]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process harvests
     */
    protected function processHarvests(bool $rebuild, int $batchSize): array
    {
        $harvests = Harvest::with('batch')->get();
        $processed = 0;
        $skipped = 0;

        foreach ($harvests as $harvest) {
            $content = $this->generateHarvestContent($harvest);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('harvests', $harvest->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'harvests',
                    $harvest->id,
                    $content,
                    $embedding,
                    [
                        'batch_id' => $harvest->batch_id,
                        'batch_code' => $harvest->batch->batch_code ?? null,
                        'harvest_date' => $harvest->harvest_date->format('Y-m-d'),
                    ]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process rooms
     */
    protected function processRooms(bool $rebuild, int $batchSize): array
    {
        $rooms = Room::with('facility')->get();
        $processed = 0;
        $skipped = 0;

        foreach ($rooms as $room) {
            $content = $this->generateRoomContent($room);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('rooms', $room->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'rooms',
                    $room->id,
                    $content,
                    $embedding,
                    ['room_name' => $room->name, 'room_type' => $room->type]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process facilities
     */
    protected function processFacilities(bool $rebuild, int $batchSize): array
    {
        $facilities = Facility::all();
        $processed = 0;
        $skipped = 0;

        foreach ($facilities as $facility) {
            $content = $this->generateFacilityContent($facility);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('facilities', $facility->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'facilities',
                    $facility->id,
                    $content,
                    $embedding,
                    ['facility_name' => $facility->name]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Process growth cycles
     */
    protected function processGrowthCycles(bool $rebuild, int $batchSize): array
    {
        $cycles = GrowthCycle::with('facility')->get();
        $processed = 0;
        $skipped = 0;

        foreach ($cycles as $cycle) {
            $content = $this->generateGrowthCycleContent($cycle);
            $hash = AiEmbedding::generateContentHash($content);

            if (!$rebuild && $this->embeddingExists('growth_cycles', $cycle->id, $hash)) {
                $skipped++;
                continue;
            }

            $embedding = $this->generateEmbedding($content);
            
            if ($embedding) {
                AiEmbedding::findOrCreateForContent(
                    'growth_cycles',
                    $cycle->id,
                    $content,
                    $embedding,
                    ['cycle_name' => $cycle->name, 'status' => $cycle->status]
                );
                $processed++;
            }

            usleep(100000);
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Generate batch content for embedding
     */
    protected function generateBatchContent(Batch $batch): string
    {
        return <<<CONTENT
Batch: {$batch->batch_code}
Strain: {$batch->strain->name} ({$batch->strain->type})
Status: {$batch->status}
Room: {$batch->room->name} ({$batch->room->type})
Plant Count: {$batch->current_plant_count} (started with {$batch->initial_plant_count})
Progress: {$batch->progress_percentage}%
Planting Date: {$batch->planting_date->format('Y-m-d')}
Expected Harvest: {$batch->expected_harvest_date?->format('Y-m-d')}
Notes: {$batch->notes}
CONTENT;
    }

    /**
     * Generate strain content
     */
    protected function generateStrainContent(Strain $strain): string
    {
        return <<<CONTENT
Strain: {$strain->name} (Code: {$strain->code})
Type: {$strain->type}
Genetics: {$strain->genetics}
Description: {$strain->description}
THC Range: {$strain->thc_min}% - {$strain->thc_max}%
CBD Range: {$strain->cbd_min}% - {$strain->cbd_max}%
Expected Yield: {$strain->expected_yield_per_plant}g per plant
Flowering Days: {$strain->expected_flowering_days}
Vegetative Days: {$strain->expected_vegetative_days}
Growth Notes: {$strain->growth_notes}
Nutrient Requirements: {$strain->nutrient_requirements}
CONTENT;
    }

    /**
     * Generate batch log content
     */
    protected function generateBatchLogContent(BatchLog $log): string
    {
        $activities = is_array($log->activities) 
            ? implode(', ', array_column($log->activities, 'activity'))
            : '';

        return <<<CONTENT
Batch Log for {$log->batch->batch_code} on {$log->log_date->format('Y-m-d')}
Activities: {$activities}
Temperature: {$log->temperature_avg}°C (min: {$log->temperature_min}, max: {$log->temperature_max})
Humidity: {$log->humidity_avg}% (min: {$log->humidity_min}, max: {$log->humidity_max})
CO2: {$log->co2_avg} ppm
pH: {$log->ph_avg}
EC: {$log->ec_avg}
Plant Count: {$log->plant_count}
Notes: {$log->notes}
CONTENT;
    }

    /**
     * Generate environmental reading content
     */
    protected function generateEnvironmentalContent(EnvironmentalReading $reading): string
    {
        return <<<CONTENT
Environmental Reading for {$reading->space_type} (ID: {$reading->space_id})
Facility: {$reading->facility->name}
Recorded: {$reading->recorded_at->format('Y-m-d H:i')}
Temperature: {$reading->temperature}°C
Humidity: {$reading->humidity}%
CO2: {$reading->co2} ppm
pH: {$reading->ph}
EC: {$reading->ec}
CONTENT;
    }

    /**
     * Generate harvest content
     */
    protected function generateHarvestContent(Harvest $harvest): string
    {
        return <<<CONTENT
Harvest for Batch {$harvest->batch->batch_code}
Harvest Date: {$harvest->harvest_date->format('Y-m-d')}
Wet Weight: {$harvest->wet_weight}g
Dry Weight: {$harvest->dry_weight}g
Trim Weight: {$harvest->trim_weight}g
Waste Weight: {$harvest->waste_weight}g
Yield Percentage: {$harvest->yield_percentage}%
Plants Harvested: {$harvest->harvested_plant_count}
Quality Notes: {$harvest->quality_notes}
CONTENT;
    }

    /**
     * Generate room content
     */
    protected function generateRoomContent(Room $room): string
    {
        $status = $room->is_active ? 'Active' : 'Inactive';
        return <<<CONTENT
Room: {$room->name} (Code: {$room->code})
Type: {$room->type}
Facility: {$room->facility->name}
Capacity: {$room->capacity} plants
Temperature Range: {$room->temperature_min}°C - {$room->temperature_max}°C
Humidity Range: {$room->humidity_min}% - {$room->humidity_max}%
CO2 Range: {$room->co2_min} - {$room->co2_max} ppm
pH Range: {$room->ph_min} - {$room->ph_max}
Status: {$status}
CONTENT;
    }

    /**
     * Generate facility content
     */
    protected function generateFacilityContent(Facility $facility): string
    {
        $status = $facility->is_active ? 'Active' : 'Inactive';
        return <<<CONTENT
Facility: {$facility->name} (Code: {$facility->code})
Location: {$facility->city}, {$facility->state}, {$facility->country}
Address: {$facility->address}
Postal Code: {$facility->postal_code}
Status: {$status}
CONTENT;
    }

    /**
     * Generate growth cycle content
     */
    protected function generateGrowthCycleContent(GrowthCycle $cycle): string
    {
        return <<<CONTENT
Growth Cycle: {$cycle->name}
Facility: {$cycle->facility->name}
Status: {$cycle->status}
Start Date: {$cycle->start_date->format('Y-m-d')}
Expected End: {$cycle->expected_end_date?->format('Y-m-d')}
Actual End: {$cycle->actual_end_date?->format('Y-m-d')}
Notes: {$cycle->notes}
CONTENT;
    }

    /**
     * Generate embedding for content
     */
    protected function generateEmbedding(string $content): ?array
    {
        $result = $this->provider->generateEmbeddings($content);
        
        if ($result['success'] && !empty($result['embeddings'])) {
            return $result['embeddings'][0];
        }

        return null;
    }

    /**
     * Check if embedding exists
     */
    protected function embeddingExists(string $table, int $id, string $hash): bool
    {
        return AiEmbedding::where('source_table', $table)
            ->where('source_id', $id)
            ->where('content_hash', $hash)
            ->exists();
    }
}

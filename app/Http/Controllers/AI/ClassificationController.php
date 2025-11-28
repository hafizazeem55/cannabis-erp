<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\PlantClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClassificationController extends Controller
{
    protected PlantClassificationService $classificationService;

    public function __construct(PlantClassificationService $classificationService)
    {
        $this->classificationService = $classificationService;
    }

    /**
     * Classify plant image
     */
    public function classify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:' . (config('ai.images.max_size_mb') * 1024),
            'batch_id' => 'nullable|exists:batches,id',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Store uploaded image
            $image = $request->file('image');
            $path = $image->store(config('ai.images.storage_path'), config('ai.images.storage_disk'));

            // Classify image
            $result = $this->classificationService->classify(
                $path,
                $request->batch_id,
                $request->room_id,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'result' => [
                    'id' => $result->id,
                    'classifications' => $result->classifications,
                    'top_label' => $result->top_label,
                    'confidence' => $result->confidence,
                    'growth_stage' => $result->growth_stage,
                    'health_status' => $result->health_status,
                    'leaf_issues' => $result->leaf_issues,
                    'strain_type_prediction' => $result->strain_type_prediction,
                    'image_url' => Storage::url($path),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get classification result by ID
     */
    public function show(int $id)
    {
        $result = auth()->user()->can('ai.use') 
            ? \App\Models\AiClassificationResult::findOrFail($id)
            : \App\Models\AiClassificationResult::where('created_by', auth()->id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Get batch classification statistics
     */
    public function batchStats(int $batchId)
    {
        $stats = $this->classificationService->getBatchClassificationStats($batchId);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * List classifications for batch
     */
    public function listByBatch(int $batchId)
    {
        $results = \App\Models\AiClassificationResult::where('batch_id', $batchId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}

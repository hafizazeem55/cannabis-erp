<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\PlantAnomalyDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AnomalyDetectionController extends Controller
{
    protected PlantAnomalyDetectionService $anomalyService;

    public function __construct(PlantAnomalyDetectionService $anomalyService)
    {
        $this->anomalyService = $anomalyService;
    }

    /**
     * Detect anomaly in uploaded image
     */
    public function detect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:' . (config('ai.images.max_size_mb') * 1024),
            'batch_id' => 'required|exists:batches,id',
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

            // Detect anomaly
            $report = $this->anomalyService->detectAnomaly(
                $path,
                $request->batch_id,
                $request->room_id,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'report' => [
                    'id' => $report->id,
                    'is_anomaly' => $report->is_anomaly,
                    'confidence' => $report->confidence,
                    'detected_issue' => $report->detected_issue,
                    'issue_description' => $report->issue_description,
                    'recommended_action' => $report->recommended_action,
                    'severity' => $report->severity,
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
     * Get anomaly report by ID
     */
    public function show(int $id)
    {
        $report = auth()->user()->can('ai.use') 
            ? \App\Models\AiAnomalyReport::findOrFail($id)
            : \App\Models\AiAnomalyReport::where('created_by', auth()->id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Get batch anomaly statistics
     */
    public function batchStats(int $batchId)
    {
        $stats = $this->anomalyService->getBatchAnomalyStats($batchId);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Review anomaly report
     */
    public function review(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'reviewed' => 'required|boolean',
            'review_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = \App\Models\AiAnomalyReport::findOrFail($id);
        
        $report->update([
            'reviewed' => $request->reviewed,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
        ]);

        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * List anomaly reports for batch
     */
    public function listByBatch(int $batchId)
    {
        $reports = \App\Models\AiAnomalyReport::where('batch_id', $batchId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }
}

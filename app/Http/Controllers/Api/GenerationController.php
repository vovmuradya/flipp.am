<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\CarGeneration;
use Illuminate\Support\Facades\Log;

class GenerationController extends Controller
{
    /**
     * Get generations for a specific car model
     * GET /api/models/{modelId}/generations
     */
    public function getGenerationsByModel($modelId)
    {
        Log::info('GenerationController: Requesting generations for model ID: ' . $modelId);

        try {
            // Find the model
            $model = CarModel::find($modelId);

            if (!$model) {
                Log::warning('Model not found: ' . $modelId);
                return response()->json(
                    ['error' => 'Model not found', 'model_id' => $modelId],
                    404
                );
            }

            Log::info('Model found: ' . $model->name_en);

            // Get all generations for this model
            $generations = $model->generations()
                ->orderBy('year_start', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            Log::info('Found ' . $generations->count() . ' generations');

            // Format the response
            $formatted = $generations->map(function ($gen) {
                $label = $gen->name ?? 'Generation';

                if ($gen->year_start) {
                    $label .= ' (' . $gen->year_start;
                    if ($gen->year_end) {
                        $label .= '-' . $gen->year_end;
                    } else {
                        $label .= '+';
                    }
                    $label .= ')';
                }

                return [
                    'value' => $gen->id,
                    'label' => $label,
                ];
            });

            return response()->json($formatted);

        } catch (\Exception $e) {
            Log::error('GenerationController Error: ' . $e->getMessage());
            Log::error('Stack: ' . $e->getTraceAsString());

            return response()->json(
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                500
            );
        }
    }
}

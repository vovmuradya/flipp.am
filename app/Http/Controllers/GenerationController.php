<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\CarGeneration;

class GenerationController extends Controller
{
    /**
     * Get generations for a specific car model
     * GET /api/models/{modelId}/generations
     */
    public function getGenerationsByModel($modelId)
    {
        try {
            // Verify the model exists
            $model = CarModel::findOrFail($modelId);

            // Get all generations for this model
            $generations = CarGeneration::where('car_model_id', $modelId)
                ->orderBy('year_start', 'asc')
                ->get();

            // Format the response
            $formattedGenerations = $generations->map(function ($gen) {
                return [
                    'value' => $gen->id,
                    'label' => $gen->name .
                        ($gen->year_start ? ' (' . $gen->year_start .
                            ($gen->year_end ? '-' . $gen->year_end : '+') . ')' : '')
                ];
            });

            return response()->json($formattedGenerations);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Model not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

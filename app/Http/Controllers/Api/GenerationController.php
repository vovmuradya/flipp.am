<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarGeneration;
use Illuminate\Http\Request;

class GenerationController extends Controller
{
    public function getGenerationsByModel($modelId)
    {
        $generations = CarGeneration::where('car_model_id', $modelId)
            ->orderBy('year_begin', 'desc')
            ->get();

        return response()->json($generations);
    }
}

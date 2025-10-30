<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CarBrandController extends Controller
{
    public function index()
    {
        Log::info('CarBrandController@index called');
        try {
            $brands = CarBrand::orderBy('name_ru')->get();
            Log::info('Found ' . $brands->count() . ' brands');
            Log::info('First brand: ' . ($brands->first() ? json_encode($brands->first()) : 'none'));
            return response()->json($brands);
        } catch (\Exception $e) {
            Log::error('Error in CarBrandController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function getModels(CarBrand $brand)
    {
        Log::info('CarBrandController@getModels called for brand: ' . $brand->id);
        try {
            $models = $brand->models()->orderBy('name_ru')->get();
            Log::info('Found ' . $models->count() . ' models');
            Log::info('First model: ' . ($models->first() ? json_encode($models->first()) : 'none'));
            return response()->json($models);
        } catch (\Exception $e) {
            Log::error('Error in CarBrandController@getModels: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class CarModelController extends Controller
{
    public function getByBrand(CarBrand $brand): JsonResponse
    {
        $currentLocale = App::getLocale();
        $langField = ($currentLocale === 'ru') ? 'name_ru' : 'name_en';

        $models = $brand->models()
            ->orderBy('name_en')
            ->get()
            ->map(function ($model) use ($langField) {
                return [
                    'value' => $model->id,
                    'label' => $model->{$langField} ?: $model->name_en,
                ];
            });

        return response()->json($models);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\CarBrand;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class CarModelController extends Controller
{
    /**
     * Получить модели для конкретной марки
     * Маршрут: /api/brands/{brand}/models
     */
    public function getByBrand(CarBrand $brand): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка загрузки моделей: ' . $e->getMessage()
            ], 500);
        }
    }
}

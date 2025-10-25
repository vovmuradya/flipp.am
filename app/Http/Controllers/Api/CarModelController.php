<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App; // App больше не нужен для locale

class CarModelController extends Controller
{
    /**
     * Возвращает модели для указанного бренда в формате { value: id, label: name_ru }
     */
    // Имя метода оставляем getByBrand, так как маршрут на него указывает
    public function getByBrand(CarBrand $brand): JsonResponse
    {
        // $currentLocale = App::getLocale(); // Удаляем ненужную логику
        // $langField = ($currentLocale === 'ru') ? 'name_ru' : 'name_en';

        // ✅ ИСПРАВЛЕНО: Сортировка и выборка по name_en
        $models = $brand->models()
            ->orderBy('name_en')
            ->get(['id', 'name_en']) // Выбираем name_en
            ->map(function ($model) {
                return [
                    'value' => $model->id,
                    // ✅ ИСПРАВЛЕНО: Используем name_en
                    'label' => $model->name_en,
                ];
            });

        return response()->json($models);
    }
    public function getGenerationsByModel(CarModel $model): JsonResponse
    {
        $generations = $model->generations() // Метод generations должен быть добавлен в CarModel.php
        ->orderBy('year_start', 'desc')
            ->get(['id', 'name_en', 'name_ru']);

        $formatted = $generations->map(function ($gen) {
            return [
                'value' => $gen->id,
                'label' => $gen->name_ru ?: $gen->name_en, // Используем русское, если есть
            ];
        });

        return response()->json($formatted);
    }
}

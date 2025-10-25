<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Http\JsonResponse;
use App\Models\CarGeneration;
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
    // Загружаем связанные поколения
    $generations = $model->generations()
        ->orderBy('year_start', 'desc') // Сортируем по году начала
        // ✅ ИСПРАВЛЕНО: Выбираем правильные колонки
        ->get(['id', 'name', 'year_start', 'year_end']);

    // Форматируем для выпадающего списка
    $formatted = $generations->map(function ($gen) {
        // Формируем label, добавляя годы, если они есть
        $label = $gen->name;
        if ($gen->year_start && $gen->year_end) {
            $label .= " ({$gen->year_start}-{$gen->year_end})";
        } elseif ($gen->year_start) {
            $label .= " ({$gen->year_start})";
        }

        return [
            'value' => $gen->id,
            'label' => $label, // Отображаем имя + годы
        ];
    });

    return response()->json($formatted);
}
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use App\Models\CarBrand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function fields(Category $category): JsonResponse
    {
        Log::info('Category ID: ' . $category->id);
        Log::info('Fields count: ' . $category->fields()->count());

        if (empty($category->slug)) {
            $category->refresh();
        }

        Log::info('API Fields: Processing category ' . $category->slug);

        // Получаем поля как коллекцию объектов (не массив!)
        $fields = $category->fields()->orderBy('id')->get();

        Log::info('Category slug: ' . $category->slug);
        foreach ($fields as $f) {
            Log::info('Field key: ' . $f->key);
        }

        $currentLocale = App::getLocale();
        $langField = ($currentLocale === 'ru') ? 'name_ru' : 'name_en';

        // Используем map для преобразования коллекции
        $fields = $fields->map(function ($field) use ($langField) {
            $is_car_brand_field = (strtolower($field->key) === 'brand');

            if ($is_car_brand_field) {
                Log::info('API Fields: CONDITION MET: Starting to load brands.');

                $brands = CarBrand::query()
                    ->orderBy('name_en')
                    ->get()
                    ->map(function ($brand) use ($langField) {
                        return [
                            'value' => $brand->id,
                            'label' => $brand->{$langField} ?: $brand->name_en,
                        ];
                    })
                    ->values()
                    ->toArray();

                Log::info('API Fields: Brands loaded count: ' . count($brands));

                // Присваиваем options к объекту поля
                $field->options = $brands;
            }

            return $field;
        });

        return response()->json($fields);
    }

    public function getRoot(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->current_name ?? (is_array($c->name) ? (array_values($c->name)[0] ?? '') : $c->name),
                    'children_count' => $c->children_count ?? 0,
                ];
            });

        return response()->json($categories);
    }

    public function getChildren(Category $category): JsonResponse
    {
        $children = $category->children()
            ->withCount('children')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->current_name ?? (is_array($c->name) ? (array_values($c->name)[0] ?? '') : $c->name),
                    'children_count' => $c->children_count ?? 0,
                ];
            });

        return response()->json($children);
    }
}

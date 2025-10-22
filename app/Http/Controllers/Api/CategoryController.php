<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use App\Models\CarBrand; // ДОБАВЛЕНО
use Illuminate\Support\Facades\App; // ДОБАВЛЕНО
use Illuminate\Support\Facades\Log;
class CategoryController extends Controller
{
    // ... (методы getRoot и getChildren остаются без изменений) ...

    public function fields(Category $category)
    {

        if (empty($category->slug)) {
            $category->refresh();
        }

        Log::info('API Fields: Processing category ' . $category->slug);

        $fields = $category->fields()->orderBy('id')->get()->toArray();

        Log::info('Category slug: ' . $category->slug);
        foreach ($fields as $f) {
            Log::info('Field key: ' . $f['key']);
        }
        $currentLocale = App::getLocale();
        $langField = ($currentLocale === 'ru') ? 'name_ru' : 'name_en';

        foreach ($fields as $key => $field) {
            $is_car_brand_field = (strtolower($field['key']) === 'brand');

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

                // ВАЖНО — без json_encode
                $fields[$key]['options'] = $brands;
            }
        }

        return response()->json($fields);
    }

    /**
     * Получить корневые (родительские) категории
     */
    public function getRoot(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->select('id', 'name')
            ->withCount('children') // Добавляем счетчик дочерних категорий
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Получить дочерние категории для указанной
     */
    public function getChildren(Category $category): JsonResponse
    {
        $children = $category->children()
            ->select('id', 'name')
            ->withCount('children') // Также добавляем счетчик
            ->orderBy('name')
            ->get();

        return response()->json($children);
    }

}

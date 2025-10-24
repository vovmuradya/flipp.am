<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CarBrand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CategoryFieldController extends Controller
{
    public function index(Category $category)
    {
        if (empty($category->slug)) {
            $category->refresh();
        }

        Log::info('API Fields: Processing category ' . $category->slug);

        // Получаем поля как коллекцию объектов
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
}

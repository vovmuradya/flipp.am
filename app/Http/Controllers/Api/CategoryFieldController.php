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

        // Преобразуем в унифицированный массив
        $result = $fields->map(function ($field) use ($currentLocale) {
            $is_car_brand_field = (strtolower($field->key) === 'brand');

            $options = $field->options ?? [];
            if ($is_car_brand_field) {
                // Загружаем марки и преобразуем в {value,label}
                $brands = CarBrand::query()
                    ->orderBy('name_en')
                    ->get()
                    ->map(function ($brand) use ($currentLocale) {
                        $label = $brand->name_en ?? $brand->name;
                        // Если в будущем понадобятся локализованные названия, добавьте соответствующие поля в модель
                        return ['value' => $brand->id, 'label' => $label];
                    })->values()->toArray();

                $options = $brands;
            }

            // Приводим name к строке (если надо локализовать, в будущем можно сделать JSON)
            $name = is_array($field->name) ? ($field->name[$currentLocale] ?? array_values($field->name)[0]) : $field->name;

            return [
                'id' => $field->id,
                'key' => $field->key,
                'name' => $name,
                'type' => $field->type,
                'is_required' => (bool)$field->is_required,
                'options' => $options,
            ];
        });

        return response()->json($result);
    }
}

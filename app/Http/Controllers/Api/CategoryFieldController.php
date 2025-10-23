<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use App\Models\Category;

class CategoryFieldController extends Controller
{
    public function index(Category $category)
    {
        // Получаем поля как коллекцию объектов, а не массив
        $fields = $category->fields()->get();

        // Проходим по каждому полю с помощью map
        $fields->map(function ($field) {
            if ($field->key === 'brand') {
                // ИСПОЛЬЗУЕМ ПРАВИЛЬНОЕ ИМЯ КОЛОНКИ 'name'
                $field->options = CarBrand::orderBy('name')
                    ->get(['id', 'name'])
                    ->toArray();
            }
            elseif ($field->key === 'model') {
                $field->options = [];
            }
            return $field;
        });

        // Возвращаем измененную коллекцию в формате JSON
        return response()->json($fields);
    }
}

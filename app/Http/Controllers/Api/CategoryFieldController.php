<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use App\Models\Category;

class CategoryFieldController extends Controller
{
    public function index(Category $category)
    {
        $fields = $category->fields()->get()->toArray();

        $brandFieldIndex = -1;
        foreach ($fields as $index => $field) {
            if ($field['key'] === 'brand') {
                $brandFieldIndex = $index;
                break;
            }
        }

        if ($brandFieldIndex !== -1) {
            // ИСПРАВЛЕНИЕ ЗДЕСЬ:
            // Мы выбираем id и name_ru, но переименовываем name_ru в 'name'
            // чтобы фронтенд-код не сломался.
            $carBrands = CarBrand::orderBy('name_ru')
                ->get(['id', 'name_ru as name']);

            $fields[$brandFieldIndex]['car_brands'] = $carBrands;
        }

        return response()->json($fields);
    }
}

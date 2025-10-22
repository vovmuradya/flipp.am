<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CarBrand;
use Illuminate\Http\JsonResponse;

class CategoryFieldController extends Controller
{
    /**
     * Get all fields for a specific category.
     */
    public function index(Category $category)
    {
        $fields = $category->fields()->get()->toArray();

        $carBrandFieldIndex = -1;
        foreach ($fields as $index => $field) {
            if ($field['key'] === 'car_brand') {
                $carBrandFieldIndex = $index;
                break;
            }
        }

        if ($carBrandFieldIndex !== -1) {
            $carBrands = CarBrand::orderBy('name')->get(['id', 'name']);
            $fields[$carBrandFieldIndex]['car_brands'] = $carBrands;
        }

        return response()->json($fields);
    }
}
//
//namespace App\Http\Controllers\Api;
//
//use App\Http\Controllers\Controller;
//use App\Models\Category;
//use Illuminate\Http\JsonResponse;
//
//class CategoryFieldController extends Controller
//{
//    /**
//     * Получить все фильтры (поля) для категории
//     * GET /api/categories/{id}/fields
//     */
//    public function getFieldsByCategory($categoryId): JsonResponse
//    {
//        $category = Category::findOrFail($categoryId);
//
//        // Если это родительская категория, нужно выбрать дочернюю
//        if ($category->children()->exists()) {
//            return response()->json([
//                'error' => 'Выберите конкретную категорию, а не родительскую',
//                'subcategories' => $category->children()
//                    ->select('id', 'name', 'slug')
//                    ->get()
//            ], 400);
//        }
//
//        // Получаем все поля для этой категории
//        $fields = $category->customFields()
//            ->select('id', 'name', 'key', 'type', 'options', 'is_required')
//            ->get()
//            ->map(function ($field) {
//                return [
//                    'id' => $field->id,
//                    'name' => $field->name,
//                    'key' => $field->key,
//                    'type' => $field->type,
//                    'is_required' => $field->is_required,
//                    'options' => $field->options ? json_decode($field->options, true) : null,
//                ];
//            });
//
//        return response()->json([
//            'category_id' => $category->id,
//            'category_name' => $category->name,
//            'category_slug' => $category->slug,
//            'fields' => $fields
//        ]);
//    }
//
//    /**
//     * Получить все родительские категории
//     * GET /api/categories/parents
//     */
//    public function getParentCategories(): JsonResponse
//    {
//        $parents = Category::whereNull('parent_id')
//            ->with(['children' => function ($q) {
//                $q->select('id', 'parent_id', 'name', 'slug');
//            }])
//            ->select('id', 'name', 'slug')
//            ->get();
//
//        return response()->json($parents);
//    }
//}

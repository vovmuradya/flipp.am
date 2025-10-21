<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '*');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSorts = ['created_at', 'price', 'views_count', 'title'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $search = Listing::search($query);

        // --- НАЧАЛО: НОВЫЙ БЛОК ОБРАБОТКИ ФИЛЬТРОВ ---

        // Стандартные фильтры
        if ($request->filled('category_id')) {
            $search->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('region_id')) {
            $search->where('region_id', $request->input('region_id'));
        }
        if ($request->filled('price_from')) {
            $search->where('price', '>=', (int)$request->input('price_from'));
        }
        if ($request->filled('price_to')) {
            $search->where('price', '<=', (int)$request->input('price_to'));
        }

        // Обработка кастомных фильтров
        if ($request->has('filters')) {
            foreach ($request->filters as $key => $value) {
                if (is_array($value)) { // Обработка диапазонов (от/до)
                    if (!empty($value['from'])) {
                        $search->where($key, '>=', (int)$value['from']);
                    }
                    if (!empty($value['to'])) {
                        $search->where($key, '<=', (int)$value['to']);
                    }
                } else if (!empty($value)) { // Обработка точных значений (select)
                    $search->where($key, $value);
                }
            }
        }

        // --- КОНЕЦ: НОВОГО БЛОКА ---

        $search->orderBy($sortBy, $sortOrder);

        $listings = $search->paginate(12)->withQueryString();
        $listings->load('category', 'region', 'media', 'user.favorites');

        $allCategories = Category::get(); // Получаем все категории для передачи в JS
        $categories = $allCategories->whereNotNull('parent_id')->sortBy('name');
        $regions = Region::where('type', 'city')->orderBy('name')->get();

        // Передаём в вид $allCategories вместо $categories для JS
        return view('search.index', [
            'listings' => $listings,
            'categories' => $categories,
            'allCategories' => $allCategories, // Для JS
            'regions' => $regions
        ]);
    }
}

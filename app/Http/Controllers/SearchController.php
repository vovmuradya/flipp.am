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

        $sortOptions = [
            'created_at' => __('По дате'),
            'price' => __('По цене'),
            'views_count' => __('По популярности'),
            'title' => __('По названию'),
        ];
        $allowedSorts = array_keys($sortOptions);
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
        if ($request->filled('currency')) {
            $search->where('currency', strtoupper($request->input('currency')));
        }
        if ($request->filled('year_from')) {
            $search->where('year', '>=', (int)$request->input('year_from'));
        }
        if ($request->filled('year_to')) {
            $search->where('year', '<=', (int)$request->input('year_to'));
        }

        if ($request->filled('listing_type')) {
            $search->where('listing_type', $request->input('listing_type'));
        }

        $origin = $request->input('origin');
        if ($origin === 'regular') {
            $search->where('is_from_auction', false);
        } elseif (in_array($origin, ['auction', 'abroad', 'transit'])) {
            $search->where('is_from_auction', true);
        }

        // Обработка кастомных фильтров
        $filters = (array) $request->input('filters', []);
        foreach ($filters as $key => $value) {
            if (is_array($value)) { // диапазоны (от/до)
                $from = $value['from'] ?? null;
                $to = $value['to'] ?? null;
                if ($from !== null && $from !== '') {
                    $search->where($key, '>=', (int) $from);
                }
                if ($to !== null && $to !== '') {
                    $search->where($key, '<=', (int) $to);
                }
            } else { // точные значения
                if ($value === null || $value === '') {
                    continue;
                }
                $search->where($key, $value);
            }
        }

        // --- КОНЕЦ: НОВОГО БЛОКА ---

        $search->orderBy($sortBy, $sortOrder);

        $listings = $search->paginate(12)->withQueryString();
        $listings->load('category', 'region', 'media', 'vehicleDetail', 'user.favorites');

        $allCategories = Category::get(); // Получаем все категории для передачи в JS
        $categories = $allCategories->whereNotNull('parent_id')->sortBy('name');
        $regions = Region::where('type', 'city')->orderBy('name')->get();

        // Передаём в вид $allCategories вместо $categories для JS
        return view('search.index', [
            'listings' => $listings,
            'categories' => $categories,
            'allCategories' => $allCategories, // Для JS
            'regions' => $regions,
            'sortOptions' => $sortOptions,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $onlyRegular = $request->boolean('only_regular');
        $onlyAuctions = $request->boolean('only_auctions');
        $originFilter = $request->string('origin')->lower();

        if ($originFilter === 'regular') {
            $onlyRegular = true;
            $onlyAuctions = false;
        } elseif (in_array($originFilter, ['auction', 'abroad', 'transit'])) {
            $onlyRegular = false;
            $onlyAuctions = true;
        }

        $query = Listing::query()
            ->with(['category', 'region', 'user', 'vehicleDetail', 'media']);

        if ($onlyAuctions) {
            $query->fromAuction()->active();
        } elseif ($onlyRegular) {
            $query->regular()->active();
        } else {
            $query->active();
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $ids = [];
            $searchFailed = false;

            try {
                $ids = Listing::search($term)->take(200)->get()->pluck('id')->toArray();
            } catch (\Throwable $e) {
                $searchFailed = true;
                Log::warning('SearchController Scout fallback', [
                    'term' => $term,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($searchFailed) {
                $this->applySearchFallback($query, $term);
            } elseif (!empty($ids)) {
                $query->whereIn('id', $ids)
                    ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
            } else {
                $this->applySearchFallback($query, $term);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }
        if ($request->filled('price_from')) {
            $query->where('price', '>=', (int) $request->input('price_from'));
        }
        if ($request->filled('price_to')) {
            $query->where('price', '<=', (int) $request->input('price_to'));
        }
        if ($request->filled('currency')) {
            $query->where('currency', strtoupper($request->input('currency')));
        }
        if ($request->filled('year_from')) {
            $query->whereHas('vehicleDetail', fn ($q) => $q->where('year', '>=', (int) $request->input('year_from')));
        }
        if ($request->filled('year_to')) {
            $query->whereHas('vehicleDetail', fn ($q) => $q->where('year', '<=', (int) $request->input('year_to')));
        }

        if ($request->filled('brand')) {
            $brand = mb_strtolower($request->input('brand'));
            $query->whereHas('vehicleDetail', fn ($q) => $q->whereRaw('LOWER(make) = ?', [$brand]));
        }

        if ($request->filled('model')) {
            $model = mb_strtolower($request->input('model'));
            $query->whereHas('vehicleDetail', fn ($q) => $q->whereRaw('LOWER(model) = ?', [$model]));
        }

        foreach (['body_type', 'transmission', 'fuel_type', 'drive_type', 'condition'] as $field) {
            if ($request->filled($field)) {
                $query->whereHas('vehicleDetail', fn ($q) => $q->where($field, $request->input($field)));
            }
        }

        if ($request->filled('engine_from')) {
            $query->whereHas('vehicleDetail', fn ($q) => $q->where('engine_displacement_cc', '>=', (int) $request->input('engine_from')));
        }

        if ($request->filled('engine_to')) {
            $query->whereHas('vehicleDetail', fn ($q) => $q->where('engine_displacement_cc', '<=', (int) $request->input('engine_to')));
        }

        $listings = $query->paginate(20)->withQueryString();

        return view('search.index', [
            'listings' => $listings,
        ]);
    }

    private function applySearchFallback($query, string $term): void
    {
        $likeTerm = '%' . str_replace(' ', '%', $term) . '%';

        $query->where(function ($subQuery) use ($likeTerm) {
            $subQuery->where('title', 'LIKE', $likeTerm)
                ->orWhere('description', 'LIKE', $likeTerm)
                ->orWhereHas('vehicleDetail', function ($vehicleQuery) use ($likeTerm) {
                    $vehicleQuery->where('make', 'LIKE', $likeTerm)
                        ->orWhere('model', 'LIKE', $likeTerm)
                        ->orWhere('vin', 'LIKE', $likeTerm);
                });
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProgrammaticSeoController extends Controller
{
    public function show(Request $request, string $path = '')
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        $filters = [
            'price_from' => null,
            'price_to' => null,
            'fuel_type' => null,
            'body_type' => null,
            'year_from' => null,
            'year_to' => null,
            'brand' => null,
            'model' => null,
        ];

        $brandDictionary = CarBrand::query()
            ->select(['name_en', 'name_ru'])
            ->get()
            ->flatMap(function ($brand) {
                return array_filter([
                    Str::lower($brand->name_en),
                    Str::lower($brand->name_ru),
                ]);
            })
            ->unique()
            ->values()
            ->all();

        foreach ($segments as $seg) {
            $lower = Str::lower($seg);

            if (preg_match('/^under-(\d+)/', $lower, $m)) {
                $filters['price_to'] = (int) $m[1];
                continue;
            }

            if (preg_match('/^over-(\d+)/', $lower, $m)) {
                $filters['price_from'] = (int) $m[1];
                continue;
            }

            if (is_numeric($seg) && (int) $seg >= 1950 && (int) $seg <= (int) date('Y') + 1) {
                $filters['year_from'] = (int) $seg;
                $filters['year_to'] = (int) $seg;
                continue;
            }

            if (in_array($lower, ['diesel', 'gasoline', 'hybrid', 'electric', 'lpg'])) {
                $filters['fuel_type'] = $lower;
                continue;
            }

            if (in_array($lower, ['suv', 'sedan', 'coupe', 'hatchback', 'wagon', 'pickup', 'minivan', 'convertible'])) {
                $filters['body_type'] = $lower;
                continue;
            }

            if (in_array($lower, $brandDictionary, true)) {
                $filters['brand'] = $seg;
                continue;
            }

            // простой эвристический split brand-model (camry, corolla)
            if ($filters['brand'] && $filters['model'] === null) {
                $filters['model'] = $seg;
            }
        }

        $query = Listing::query()
            ->with(['vehicleDetail', 'media'])
            ->active();

        if ($filters['price_from']) {
            $query->where('price', '>=', $filters['price_from']);
        }
        if ($filters['price_to']) {
            $query->where('price', '<=', $filters['price_to']);
        }
        if ($filters['fuel_type']) {
            $query->whereHas('vehicleDetail', fn($q) => $q->where('fuel_type', $filters['fuel_type']));
        }
        if ($filters['body_type']) {
            $query->whereHas('vehicleDetail', fn($q) => $q->where('body_type', $filters['body_type']));
        }
        if ($filters['year_from']) {
            $query->whereHas('vehicleDetail', fn($q) => $q->where('year', '>=', $filters['year_from']));
        }
        if ($filters['year_to']) {
            $query->whereHas('vehicleDetail', fn($q) => $q->where('year', '<=', $filters['year_to']));
        }
        if ($filters['brand']) {
            $query->whereHas('vehicleDetail', function ($q) use ($filters) {
                $q->whereRaw('LOWER(make) = ?', [Str::lower($filters['brand'])]);
                if ($filters['model']) {
                    $q->whereRaw('LOWER(model) = ?', [Str::lower($filters['model'])]);
                }
            });
        }

        $listings = $query->paginate(24)->withQueryString();

        return view('pseo.landing', [
            'listings' => $listings,
            'filters' => $filters,
            'path' => $path,
        ]);
    }
}

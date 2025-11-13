<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\ListingIndexRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    use ApiResponse;

    public function index(ListingIndexRequest $request)
    {
        $query = Listing::query()
            ->active()
            ->with(['region', 'user', 'vehicleDetail', 'media'])
            ->withCount(['favorites as favorites_count']);

        $userId = $request->user()?->id;
        if ($userId) {
            $query->withCount([
                'favorites as is_favorited' => function (Builder $builder) use ($userId) {
                    $builder->where('user_id', $userId);
                },
            ]);
        }

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request->input('sort'));

        $perPage = $request->integer('per_page', 20);
        $perPage = $perPage > 50 ? 50 : $perPage;

        $paginator = $query->paginate($perPage)->withQueryString();

        return ListingResource::collection($paginator)->additional([
            'status' => 'success',
        ]);
    }

    public function show(Request $request, Listing $listing)
    {
        $listing->loadMissing(['region', 'user', 'vehicleDetail', 'media']);

        if ($request->user()) {
            $listing->loadCount([
                'favorites as is_favorited' => fn ($q) => $q->where('user_id', $request->user()->id),
            ]);
        }

        $listing->loadCount('favorites');

        return $this->success(new ListingResource($listing));
    }

    private function applyFilters(Builder $query, ListingIndexRequest $request): Builder
    {
        $filters = $request->validated();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }

        if (!empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }

        if (!empty($filters['price_from'])) {
            $query->where('price', '>=', $filters['price_from']);
        }

        if (!empty($filters['price_to'])) {
            $query->where('price', '<=', $filters['price_to']);
        }

        if (!empty($filters['q'])) {
            $search = mb_strtolower($filters['q']);
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('vehicleDetail', function (Builder $vehicleQuery) use ($search) {
                        $vehicleQuery
                            ->whereRaw('LOWER(make) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(model) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        if (array_key_exists('is_copart', $filters) && $filters['is_copart'] !== null) {
            if ($filters['is_copart']) {
                $query->whereHas('vehicleDetail', fn ($q) => $q->where('is_from_auction', true));
            } else {
                $query->where(function (Builder $builder) {
                    $builder
                        ->whereDoesntHave('vehicleDetail')
                        ->orWhereHas('vehicleDetail', fn ($q) => $q->where('is_from_auction', false));
                });
            }
        }

        if ($filters['only_buy_now'] ?? false) {
            $query->whereHas('vehicleDetail', fn ($q) => $q->whereNotNull('buy_now_price'));
        }

        $query = $this->applyVehicleFilters($query, $filters);

        return $query;
    }

    private function applyVehicleFilters(Builder $query, array $filters): Builder
    {
        $hasVehicleFilters = collect([
            'brand', 'model', 'body_type', 'transmission', 'fuel_type',
            'year_from', 'year_to', 'mileage_from', 'mileage_to',
        ])->contains(function ($key) use ($filters) {
            return !empty($filters[$key]);
        });

        if (! $hasVehicleFilters) {
            return $query;
        }

        return $query->whereHas('vehicleDetail', function (Builder $vehicleQuery) use ($filters) {
            if (!empty($filters['brand'])) {
                $brand = mb_strtolower($filters['brand']);
                $vehicleQuery->whereRaw('LOWER(make) = ?', [$brand]);
            }

            if (!empty($filters['model'])) {
                $model = mb_strtolower($filters['model']);
                $vehicleQuery->whereRaw('LOWER(model) = ?', [$model]);
            }

            if (!empty($filters['body_type'])) {
                $vehicleQuery->where('body_type', $filters['body_type']);
            }

            if (!empty($filters['transmission'])) {
                $vehicleQuery->where('transmission', $filters['transmission']);
            }

            if (!empty($filters['fuel_type'])) {
                $vehicleQuery->where('fuel_type', $filters['fuel_type']);
            }

            if (!empty($filters['year_from'])) {
                $vehicleQuery->where('year', '>=', $filters['year_from']);
            }

            if (!empty($filters['year_to'])) {
                $vehicleQuery->where('year', '<=', $filters['year_to']);
            }

            if (!empty($filters['mileage_from'])) {
                $vehicleQuery->where('mileage', '>=', $filters['mileage_from']);
            }

            if (!empty($filters['mileage_to'])) {
                $vehicleQuery->where('mileage', '<=', $filters['mileage_to']);
            }
        });
    }

    private function applySorting(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'created_at_asc' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}

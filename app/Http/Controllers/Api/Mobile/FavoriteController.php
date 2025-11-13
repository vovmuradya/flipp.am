<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = $request->integer('per_page', 20);
        $perPage = $perPage > 50 ? 50 : $perPage;

        $paginator = $user->favorites()
            ->with(['region', 'user', 'vehicleDetail', 'media'])
            ->withCount([
                'favorites as favorites_count',
                'favorites as is_favorited' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->paginate($perPage)
            ->withQueryString();

        return ListingResource::collection($paginator)->additional([
            'status' => 'success',
        ]);
    }

    public function store(Request $request, Listing $listing)
    {
        $request->user()->favorites()->syncWithoutDetaching($listing->id);

        return $this->success(message: __('Добавлено в избранное.'));
    }

    public function destroy(Request $request, Listing $listing)
    {
        $request->user()->favorites()->detach($listing->id);

        return $this->success(message: __('Удалено из избранного.'));
    }
}

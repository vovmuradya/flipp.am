<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\MobileListingStoreRequest;
use App\Http\Requests\Api\Mobile\MobileListingUpdateRequest;
use App\Http\Resources\ListingResource;
use App\Jobs\ExpireAuctionListing;
use App\Jobs\ImportAuctionPhotos;
use App\Models\Listing;
use App\Models\User;
use App\Support\VehicleAttributeOptions;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MyListingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min($request->integer('per_page', 20) ?? 20, 50));

        $paginator = $user->listings()
            ->with(['region', 'user', 'vehicleDetail', 'media'])
            ->withCount(['favorites as favorites_count'])
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->input('status')))
            ->when($request->filled('listing_type'), fn (Builder $q) => $q->where('listing_type', $request->input('listing_type')))
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return ListingResource::collection($paginator)->additional([
            'status' => 'success',
        ]);
    }

    public function auctions(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min($request->integer('per_page', 20) ?? 20, 50));

        $paginator = $user->listings()
            ->with(['region', 'user', 'vehicleDetail', 'media'])
            ->withCount(['favorites as favorites_count'])
            ->where(function (Builder $q) {
                $q->where('is_from_auction', true)
                    ->orWhereHas('vehicleDetail', fn (Builder $detail) => $detail->where('is_from_auction', true));
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return ListingResource::collection($paginator)->additional([
            'status' => 'success',
        ]);
    }

    public function store(MobileListingStoreRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($response = $this->ensurePhoneVerified($user)) {
            return $response;
        }

        try {
            $this->authorize('create', Listing::class);
        } catch (AuthorizationException $e) {
            return $this->error(__('Превышен лимит активных объявлений или роль пользователя запрещает создание.'), 403);
        }

        $isFromAuction = $this->determineAuctionFlag($request);

        try {
            DB::beginTransaction();

            $listing = new Listing();
            $listing->user_id = $user->id;
            $listing->slug = Listing::generateUniqueSlug($request->input('title', (string) Str::uuid()));

            $this->fillListing($listing, $request->validated(), $isFromAuction, false);
            $listing->save();

            $this->syncVehicleDetail($listing, (array) $request->input('vehicle', []), $isFromAuction);
            $this->attachUploadedImages($listing, $request);
            $this->handleAuctionPhotos($listing, (array) $request->input('auction_photos', []));
            $this->scheduleAuctionExpiration($listing);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('📱 Mobile listing store failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(__('Не удалось сохранить объявление.'), 500);
        }

        $listing->load(['region', 'user', 'vehicleDetail', 'media'])->loadCount('favorites');

        return $this->created(new ListingResource($listing), __('Объявление создано.'));
    }

    public function update(MobileListingUpdateRequest $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $isFromAuction = $this->determineAuctionFlag($request, $listing);

        try {
            DB::beginTransaction();

            $vehicleFlagProvided = data_get($request->input('vehicle', []), 'is_from_auction');

            if ($listing->isFromAuction() && $request->input('from_auction') === null && $vehicleFlagProvided === null) {
                $this->updateAuctionListing($listing, $request);
            } else {
                $this->fillListing($listing, $request->validated(), $isFromAuction, true);
                $listing->save();
            }

            if ($listing->listing_type === 'vehicle' || $request->has('vehicle')) {
                $this->syncVehicleDetail($listing, (array) $request->input('vehicle', []), $isFromAuction);
            } elseif ($listing->vehicleDetail && $listing->listing_type !== 'vehicle') {
                $listing->vehicleDetail()->delete();
            }

            $this->attachUploadedImages($listing, $request);

            if ($request->has('auction_photos')) {
                $this->handleAuctionPhotos($listing, (array) $request->input('auction_photos', []), true);
            }

            $this->scheduleAuctionExpiration($listing);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('📱 Mobile listing update failed', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(__('Не удалось обновить объявление.'), 500);
        }

        $listing->load(['region', 'user', 'vehicleDetail', 'media'])->loadCount('favorites');

        return $this->success(new ListingResource($listing), __('Объявление обновлено.'));
    }

    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return $this->success(message: __('Объявление удалено.'));
    }

    public function bump(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $user = $request->user();
        $intervalDays = $user->getBumpIntervalDays();
        $nextAllowed = $listing->last_bumped_at
            ? $listing->last_bumped_at->copy()->addDays($intervalDays)
            : null;

        if ($intervalDays > 0 && $nextAllowed && now()->lessThan($nextAllowed)) {
            return $this->error(
                __('Повторный подъём будет доступен через :time.', [
                    'time' => $nextAllowed->diffForHumans(now(), true),
                ]),
                422
            );
        }

        $listing->last_bumped_at = now();
        $listing->updated_at = now();
        $listing->save();

        $listing->load(['region', 'user', 'vehicleDetail', 'media'])->loadCount('favorites');

        return $this->success(new ListingResource($listing), __('Объявление поднято в выдаче.'));
    }

    private function ensurePhoneVerified(User $user): ?JsonResponse
    {
        if (empty($user->phone)) {
            return $this->error(__('Добавьте номер телефона, чтобы публиковать объявления.'), 422);
        }

        return null;
    }

    private function fillListing(Listing $listing, array $data, bool $isFromAuction, bool $isUpdate): void
    {
        if (!$isUpdate || array_key_exists('title', $data)) {
            $listing->title = $data['title'] ?? $listing->title;
        }

        if (!$isUpdate || array_key_exists('description', $data)) {
            $listing->description = $data['description'] ?? $listing->description;
        }

        if (!$isUpdate || array_key_exists('price', $data)) {
            $listing->price = $data['price'] ?? $listing->price;
        }

        if (!$isUpdate || array_key_exists('category_id', $data)) {
            $listing->category_id = $data['category_id'] ?? $listing->category_id;
        }

        if (!$isUpdate || array_key_exists('region_id', $data)) {
            $listing->region_id = $data['region_id'] ?? null;
        }

        if (!$isUpdate || array_key_exists('currency', $data)) {
            $listing->currency = strtoupper($data['currency'] ?? $listing->currency ?? 'USD');
        }

        if (!$isUpdate || array_key_exists('listing_type', $data)) {
            $listing->listing_type = $data['listing_type'] ?? $listing->listing_type ?? 'vehicle';
        }

        if (!$isUpdate || array_key_exists('language', $data)) {
            $listing->language = $data['language'] ?? $listing->language ?? app()->getLocale();
        }

        if (!$isUpdate || array_key_exists('status', $data)) {
            $listing->status = $data['status'] ?? $listing->status ?? 'active';
        }

        if (!$isUpdate || array_key_exists('from_auction', $data) || array_key_exists('vehicle', $data)) {
            $listing->is_from_auction = $isFromAuction;
        }
    }

    private function updateAuctionListing(Listing $listing, Request $request): void
    {
        $payload = $request->only(['price', 'description']);

        if (array_key_exists('price', $payload)) {
            $listing->price = $payload['price'];
        }

        if (array_key_exists('description', $payload)) {
            $listing->description = $payload['description'];
        }

        $listing->save();
    }

    private function syncVehicleDetail(Listing $listing, array $vehicleData, bool $isFromAuction): void
    {
        if ($listing->listing_type !== 'vehicle' && empty($vehicleData)) {
            return;
        }

        $detail = $listing->vehicleDetail ?: $listing->vehicleDetail()->make();

        $detail->make = $vehicleData['make'] ?? $detail->make ?? 'Неизвестно';
        $detail->model = $vehicleData['model'] ?? $detail->model ?? 'Неизвестно';
        $detail->year = $vehicleData['year'] ?? $detail->year;
        $detail->mileage = $vehicleData['mileage'] ?? $detail->mileage;
        $detail->body_type = $vehicleData['body_type'] ?? $detail->body_type;
        $detail->transmission = $vehicleData['transmission'] ?? $detail->transmission;
        $detail->fuel_type = $vehicleData['fuel_type'] ?? $detail->fuel_type;
        $detail->engine_displacement_cc = $vehicleData['engine_displacement_cc'] ?? $detail->engine_displacement_cc;

        if (!empty($vehicleData['exterior_color'])) {
            $detail->exterior_color = VehicleAttributeOptions::colorLabel($vehicleData['exterior_color'])
                ?? $vehicleData['exterior_color'];
        }

        $auctionEndsAt = $vehicleData['auction_ends_at'] ?? null;
        $detail->auction_ends_at = $auctionEndsAt ? Carbon::parse($auctionEndsAt) : $detail->auction_ends_at;
        $detail->is_from_auction = $vehicleData['is_from_auction'] ?? $isFromAuction;
        $detail->source_auction_url = $vehicleData['source_auction_url'] ?? $detail->source_auction_url;
        $detail->buy_now_price = $vehicleData['buy_now_price'] ?? $detail->buy_now_price;
        $detail->buy_now_currency = $vehicleData['buy_now_currency'] ?? $detail->buy_now_currency;
        $detail->operational_status = $vehicleData['operational_status'] ?? $detail->operational_status;

        $listing->vehicleDetail()->save($detail);
    }

    private function attachUploadedImages(Listing $listing, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ((array) $request->file('images') as $image) {
            if ($image) {
                $listing
                    ->addMedia($image)
                    ->withResponsiveImages()
                    ->toMediaCollection('images');
            }
        }
    }

    private function handleAuctionPhotos(Listing $listing, array $photos, bool $afterResponse = false): void
    {
        $photoUrls = collect($photos)
            ->filter(function ($url) {
                if (!is_string($url)) {
                    return false;
                }

                $decoded = urldecode($url);

                return !str_contains($decoded, 'placeholder.com')
                    && !str_contains($decoded, 'No+Image');
            })
            ->values()
            ->all();

        if (empty($photoUrls)) {
            return;
        }

        if ($listing->vehicleDetail
            && Schema::hasColumn('vehicle_details', 'preview_image_url')
            && empty($listing->vehicleDetail->preview_image_url)) {
            $listing->vehicleDetail->update([
                'preview_image_url' => $photoUrls[0],
            ]);
        }

        if (Schema::hasColumn('listings', 'auction_photo_urls')) {
            $listing->auction_photo_urls = $photoUrls;
            $listing->save();
        }

        if (config('queue.default') === 'sync') {
            ImportAuctionPhotos::dispatchSync($listing->id, $photoUrls);
            return;
        }

        if ($afterResponse && method_exists(ImportAuctionPhotos::class, 'dispatchAfterResponse')) {
            ImportAuctionPhotos::dispatchAfterResponse($listing->id, $photoUrls);
            return;
        }

        ImportAuctionPhotos::dispatch($listing->id, $photoUrls);
    }

    private function scheduleAuctionExpiration(Listing $listing): void
    {
        $detail = $listing->vehicleDetail;

        if (!$detail || !$detail->auction_ends_at || config('queue.default') === 'sync') {
            return;
        }

        $end = $detail->auction_ends_at instanceof Carbon
            ? $detail->auction_ends_at
            : Carbon::parse($detail->auction_ends_at);

        if (!$end || !$end->isFuture()) {
            return;
        }

        $job = new ExpireAuctionListing($listing->id);
        $job->delay($end);

        dispatch($job);
    }

    private function determineAuctionFlag(Request $request, ?Listing $listing = null): bool
    {
        $explicit = $request->input('from_auction');
        if ($explicit !== null) {
            return (bool) $explicit;
        }

        $vehicleFlag = data_get($request->input('vehicle', []), 'is_from_auction');
        if ($vehicleFlag !== null) {
            return (bool) $vehicleFlag;
        }

        return $listing?->is_from_auction ?? false;
    }
}

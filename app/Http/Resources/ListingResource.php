<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Listing */
class ListingResource extends JsonResource
{
    public function toArray($request): array
    {
        $vehicle = $this->vehicleDetail;
        $photoUrls = $this->photoUrls();
        $primaryPhoto = $photoUrls[0] ?? null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'listing_type' => $this->listing_type,
            'price' => [
                'amount' => $this->price !== null ? (float) $this->price : null,
                'currency' => $this->currency,
            ],
            'is_buy_now_available' => $vehicle?->buy_now_price !== null,
            'buy_now_price' => $vehicle?->buy_now_price !== null ? (float) $vehicle->buy_now_price : null,
            'buy_now_currency' => $vehicle?->buy_now_currency,
            'current_bid_price' => $vehicle?->current_bid_price !== null ? (float) $vehicle->current_bid_price : null,
            'current_bid_currency' => $vehicle?->current_bid_currency,
            'current_bid_fetched_at' => $vehicle?->current_bid_fetched_at?->toIso8601String(),
            'operational_status' => $vehicle?->operational_status,
            'region' => $this->when($this->relationLoaded('region') || $this->region, function () {
                return [
                    'id' => $this->region?->id,
                    'name' => $this->region?->localized_name ?? $this->region?->name,
                ];
            }),
            'seller' => $this->when($this->relationLoaded('user') || $this->user, function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'phone' => $this->user?->phone,
                    'role' => $this->user?->role,
                ];
            }),
            'vehicle' => $vehicle ? [
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'mileage' => $vehicle->mileage,
                'body_type' => $vehicle->body_type,
                'transmission' => $vehicle->transmission,
                'fuel_type' => $vehicle->fuel_type,
                'engine_displacement_cc' => $vehicle->engine_displacement_cc,
                'exterior_color' => $vehicle->exterior_color,
                'is_from_auction' => (bool) $vehicle->is_from_auction,
                'auction_ends_at' => $vehicle->auction_ends_at?->toIso8601String(),
                'source_auction_url' => $vehicle->source_auction_url,
                'current_bid_price' => $vehicle->current_bid_price !== null ? (float) $vehicle->current_bid_price : null,
                'current_bid_currency' => $vehicle->current_bid_currency,
                'current_bid_fetched_at' => $vehicle->current_bid_fetched_at?->toIso8601String(),
            ] : null,
            'photos' => [
                'primary' => $primaryPhoto,
                'all' => $photoUrls,
            ],
            'is_favorite' => (bool) ($this->is_favorited ?? false),
            'favorites_count' => (int) ($this->favorites_count ?? 0),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }

    private function photoUrls(): array
    {
        $photoCollections = collect();

        if ($this->relationLoaded('media')) {
            $photoCollections = $this->media;
        } elseif (method_exists($this->resource, 'getMedia')) {
            $photoCollections = $this->getMedia();
        }

        $urls = $photoCollections
            ->whereIn('collection_name', ['images', 'auction_photos'])
            ->sortBy('order_column')
            ->map(function ($media) {
                $conversion = $media->hasGeneratedConversion('medium') ? 'medium' : '';

                return $conversion
                    ? $media->getUrl($conversion)
                    : $media->getUrl();
            })
            ->values()
            ->all();

        if (empty($urls) && !empty($this->auction_photo_urls)) {
            $candidate = is_array($this->auction_photo_urls)
                ? $this->auction_photo_urls
                : json_decode($this->auction_photo_urls, true);

            if (is_array($candidate)) {
                foreach ($candidate as $url) {
                    if (is_string($url) && trim($url) !== '') {
                        $urls[] = trim($url);
                    }
                }
            }
        }

        if (empty($urls)) {
            $vehicle = $this->vehicleDetail;
            $preview = $vehicle?->preview_image_url ?? $vehicle?->main_image_url;

            if ($preview) {
                $urls[] = $preview;
            }
        }

        if (empty($urls)) {
            $urls[] = asset('images/no-image.jpg');
        }

        return array_values(array_unique($urls));
    }
}

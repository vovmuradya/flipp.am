<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        $listing = $this->listing;
        $counterparty = $this->counterparty;
        $thumbnail = $this->resolveThumbnail($listing);

        return [
            'listing' => [
                'id' => $listing?->id,
                'title' => $listing?->title,
                'price' => $listing?->price,
                'currency' => $listing?->currency,
                'thumbnail' => $thumbnail,
            ],
            'counterparty' => [
                'id' => $counterparty?->id,
                'name' => $counterparty?->name,
                'avatar' => $counterparty?->avatar,
                'phone_verified' => $counterparty?->phone_verified_at !== null,
            ],
            'last_message' => new MessageResource($this->last_message),
            'unread_count' => $this->unread_count,
        ];
    }

    private function resolveThumbnail($listing): ?string
    {
        if (!$listing) {
            return null;
        }

        if ($listing->relationLoaded('media')) {
            $media = $listing->media
                ?->whereIn('collection_name', ['images', 'auction_photos'])
                ->sortBy('order_column')
                ->first();

            if ($media) {
                return $media->hasGeneratedConversion('medium')
                    ? $media->getUrl('medium')
                    : $media->getUrl();
            }
        }

        $vehicle = $listing->vehicleDetail ?? null;

        return $vehicle?->preview_image_url
            ?? $vehicle?->main_image_url
            ?? null;
    }
}

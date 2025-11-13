<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MobileListingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return $this->baseRules(false);
    }

    protected function baseRules(bool $isUpdate): array
    {
        $textPresenceRule = $isUpdate ? 'sometimes' : 'required';
        $maxImages = $this->user()?->getMaxPhotosPerListing() ?? 6;
        $imagesRule = $isUpdate ? ['sometimes', 'array', 'max:'.$maxImages] : ['nullable', 'array', 'max:'.$maxImages];
        $imageFileRule = $isUpdate
            ? ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120']
            : ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'];

        $rules = [
            'title' => [$textPresenceRule, 'string', 'min:3', 'max:255'],
            'description' => [$textPresenceRule, 'string', 'min:20', 'max:5000'],
            'price' => [$textPresenceRule, 'numeric', 'min:0', 'max:999999999'],
            'currency' => ['nullable', 'string', Rule::in(['USD', 'AMD', 'RUB'])],
            'category_id' => [$textPresenceRule, 'exists:categories,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'listing_type' => ['nullable', Rule::in(['vehicle', 'parts'])],
            'status' => ['nullable', Rule::in(['active', 'moderation', 'sold', 'archived', 'draft'])],
            'language' => ['nullable', Rule::in(['ru', 'en', 'hy'])],
            'from_auction' => ['nullable', 'boolean'],
            'images' => $imagesRule,
            'images.*' => $imageFileRule,
            'auction_photos' => ['nullable', 'array', 'max:25'],
            'auction_photos.*' => ['string', 'url', 'max:512'],
            'vehicle' => ['nullable', 'array'],
        ];

        $vehicleRules = $this->vehicleRules($isUpdate);

        return array_merge($rules, $vehicleRules);
    }

    protected function vehicleRules(bool $isUpdate): array
    {
        if ($isUpdate) {
            return [
                'vehicle.make' => ['sometimes', 'nullable', 'string', 'max:100'],
                'vehicle.model' => ['sometimes', 'nullable', 'string', 'max:100'],
                'vehicle.year' => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
                'vehicle.mileage' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'vehicle.body_type' => ['sometimes', 'nullable', 'string', 'max:50'],
                'vehicle.transmission' => ['sometimes', 'nullable', Rule::in(['automatic', 'manual', 'cvt', 'semi-automatic'])],
                'vehicle.fuel_type' => ['sometimes', 'nullable', Rule::in(['gasoline', 'diesel', 'hybrid', 'electric', 'lpg'])],
                'vehicle.engine_displacement_cc' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'vehicle.exterior_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'vehicle.is_from_auction' => ['sometimes', 'nullable', 'boolean'],
                'vehicle.source_auction_url' => ['sometimes', 'nullable', 'url', 'max:512'],
                'vehicle.auction_ends_at' => ['sometimes', 'nullable', 'date'],
                'vehicle.buy_now_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'vehicle.buy_now_currency' => ['sometimes', 'nullable', 'string', 'max:4'],
                'vehicle.operational_status' => ['sometimes', 'nullable', 'string', 'max:120'],
            ];
        }

        return [
            'vehicle.make' => ['required_unless:vehicle.is_from_auction,1', 'nullable', 'string', 'max:100'],
            'vehicle.model' => ['required_unless:vehicle.is_from_auction,1', 'nullable', 'string', 'max:100'],
            'vehicle.year' => ['required_unless:vehicle.is_from_auction,1', 'nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'vehicle.mileage' => ['nullable', 'integer', 'min:0'],
            'vehicle.body_type' => ['nullable', 'string', 'max:50'],
            'vehicle.transmission' => ['nullable', Rule::in(['automatic', 'manual', 'cvt', 'semi-automatic'])],
            'vehicle.fuel_type' => ['nullable', Rule::in(['gasoline', 'diesel', 'hybrid', 'electric', 'lpg'])],
            'vehicle.engine_displacement_cc' => ['nullable', 'integer', 'min:0'],
            'vehicle.exterior_color' => ['nullable', 'string', 'max:50'],
            'vehicle.is_from_auction' => ['nullable', 'boolean'],
            'vehicle.source_auction_url' => ['nullable', 'url', 'max:512'],
            'vehicle.auction_ends_at' => ['nullable', 'date'],
            'vehicle.buy_now_price' => ['nullable', 'numeric', 'min:0'],
            'vehicle.buy_now_currency' => ['nullable', 'string', 'max:4'],
            'vehicle.operational_status' => ['nullable', 'string', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $vehicle = $this->input('vehicle', []);
        if (is_array($vehicle)) {
            if (array_key_exists('is_from_auction', $vehicle)) {
                $vehicle['is_from_auction'] = $this->toBoolean($vehicle['is_from_auction']);
            }
        }

        $this->merge([
            'from_auction' => $this->toBoolean($this->input('from_auction')),
            'vehicle' => $vehicle,
        ]);
    }

    protected function toBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

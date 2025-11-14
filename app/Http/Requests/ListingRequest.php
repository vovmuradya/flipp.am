<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\RequiredLanguage;

class ListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $maxImages = auth()->user()?->getMaxPhotosPerListing() ?? 6;

        $rules = [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'currency' => ['nullable', 'string', Rule::in(['USD', 'AMD', 'RUB'])],
            'category_id' => ['required', 'exists:categories,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'images' => ['nullable', 'array', 'max:'.$maxImages],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'custom_fields' => ['sometimes', 'array'],
            'custom_fields.*' => ['sometimes'],

            // ТЗ v2.1: listing_type
            'listing_type' => ['sometimes', 'in:vehicle,parts'],
        ];

        if ($this->boolean('from_auction') || $this->input('vehicle.is_from_auction') == 1) {
            $rules['region_id'] = ['nullable', 'exists:regions,id'];
        }

        $listing = $this->route('listing');
        if (in_array($this->method(), ['PUT','PATCH']) && $listing && method_exists($listing, 'isFromAuction') && $listing->isFromAuction()) {
            $rules['title'] = ['sometimes', 'string', 'min:3', 'max:255'];
            $rules['category_id'] = ['sometimes', 'exists:categories,id'];
        }

        if ($this->input('listing_type') === 'vehicle') {
            $vehicleRules = [
                'vehicle.make' => ['required', 'string', 'max:100'],
                'vehicle.model' => ['required', 'string', 'max:100'],
                'vehicle.year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
                'vehicle.mileage' => ['nullable', 'integer', 'min:0'],
                'vehicle.body_type' => ['nullable', 'string', 'max:50'],
                'vehicle.transmission' => ['nullable', 'in:automatic,manual,cvt,semi-automatic'],
                'vehicle.fuel_type' => ['nullable', 'in:gasoline,diesel,hybrid,electric,lpg'],
                'vehicle.engine_displacement_cc' => ['nullable', 'integer', 'min:0'],
                'vehicle.exterior_color' => ['nullable', 'string', 'max:50'],
                'vehicle.is_from_auction' => ['nullable', 'boolean'],
                'vehicle.source_auction_url' => ['nullable', 'url', 'max:512'],
                'vehicle.auction_ends_at' => ['nullable', 'date'],
                'vehicle.buy_now_price' => ['nullable', 'numeric', 'min:0'],
                'vehicle.buy_now_currency' => ['nullable', 'string', 'max:4'],
            ];
            foreach ($vehicleRules as $k => $v) {
                $rules[$k] = $v;
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => 'Заголовок обязателен',
            'title.min' => 'Заголовок должен быть не менее 3 символов',
            'description.required' => 'Описание обязательно',
            'description.min' => 'Описание должно быть не менее 20 символов',
            'price.required' => 'Цена обязательна',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'category_id.required' => 'Выберите категорию',

            'vehicle.make.required' => 'Марка обязательна.',
            'vehicle.model.required' => 'Модель обязательна.',
        ];
    }
}

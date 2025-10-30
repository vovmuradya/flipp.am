<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RequiredLanguage;

class ListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'category_id' => ['required', 'exists:categories,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'images' => ['nullable', 'array', 'max:' . (auth()->user()?->getMaxPhotosPerListing() ?? 6)],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'custom_fields' => ['array'],
            'custom_fields.*' => ['sometimes', 'required'],

            // ТЗ v2.1: listing_type
            'listing_type' => ['sometimes', 'in:vehicle,parts'],
        ];

        // ТЗ v2.1: Если это объявление об автомобиле - добавляем валидацию vehicle_details
        if ($this->input('listing_type') === 'vehicle') {
            $rules = array_merge($rules, [
                'make' => ['required', 'string', 'max:100'],
                'model' => ['required', 'string', 'max:100'],
                'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
                'mileage' => ['required', 'integer', 'min:0'],
                'body_type' => ['nullable', 'string', 'max:50'],
                'transmission' => ['nullable', 'in:automatic,manual,cvt,semi-automatic'],
                'fuel_type' => ['nullable', 'in:gasoline,diesel,hybrid,electric,lpg'],
                'engine_displacement_cc' => ['nullable', 'integer', 'min:0'],
                'exterior_color' => ['nullable', 'string', 'max:50'],
                'is_from_auction' => ['nullable', 'boolean'],
                'source_auction_url' => ['nullable', 'url', 'max:512'],
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => 'Заголовок обязателен',
            'title.min' => 'Заголовок должен быть не менее 10 символов',
            'description.required' => 'Описание обязательно',
            'description.min' => 'Описание должно быть не менее 50 символов',
            'price.required' => 'Цена обязательна',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'category_id.required' => 'Выберите категорию',
            'region_id.required' => 'Выберите регион',
            'images.required' => 'Добавьте хотя бы одно изображение',
            'images.*.image' => 'Файл должен быть изображением',
            'images.*.max' => 'Размер изображения не должен превышать 5MB',
        ];
    }
}

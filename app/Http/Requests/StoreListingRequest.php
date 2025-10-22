<?php

namespace App\Http\Requests;

use App\Rules\RequiredLanguage;
use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Listing::class);
    }

    public function rules(): array
    {
        $maxImages = auth()->user()->role === 'agency' ? 12 : 6;

        return [
            'title' => ['required', 'string', 'min:3', 'max:100', new RequiredLanguage()],
            'description' => ['required', 'string', 'min:20', 'max:5000', new RequiredLanguage()],
            'category_id' => ['required', 'exists:categories,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'images' => ['nullable', 'array', 'max:' . $maxImages],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок обязателен.',
            'title.min' => 'Заголовок должен содержать минимум 3 символа.',
            'description.required' => 'Описание обязательно.',
            'description.min' => 'Описание должно содержать минимум 20 символов.',
            'price.required' => 'Укажите цену.',
            'price.numeric' => 'Цена должна быть числом.',
        ];
    }
}

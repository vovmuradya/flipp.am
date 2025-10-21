<?php

namespace App\Http\Requests;

use App\Rules\RequiredLanguage;
use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:100', new RequiredLanguage()], // <-- Добавлено
            'description' => ['required', 'string', 'min:20', 'max:5000', new RequiredLanguage()], // <-- Добавлено
            'category_id' => ['required', 'exists:categories,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'price' => ['required', 'numeric', 'max:9999999999.99'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}

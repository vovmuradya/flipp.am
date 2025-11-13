<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class ListingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => ['nullable', 'integer', 'min:0'],
            'year_from' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'year_to' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'mileage_from' => ['nullable', 'integer', 'min:0'],
            'mileage_to' => ['nullable', 'integer', 'min:0'],
            'listing_type' => ['nullable', 'in:vehicle,parts'],
            'is_copart' => ['nullable', 'boolean'],
            'only_buy_now' => ['nullable', 'boolean'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:150'],
            'body_type' => ['nullable', 'string', 'max:100'],
            'transmission' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:created_at_desc,created_at_asc,price_desc,price_asc'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_copart' => $this->toBoolean($this->input('is_copart')),
            'only_buy_now' => $this->toBoolean($this->input('only_buy_now')),
        ]);
    }

    private function toBoolean($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

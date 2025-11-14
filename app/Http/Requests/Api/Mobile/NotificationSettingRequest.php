<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages' => ['sometimes', 'boolean'],
            'auctions' => ['sometimes', 'boolean'],
            'listings' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'messages' => $this->toBool($this->input('messages')),
            'auctions' => $this->toBool($this->input('auctions')),
            'listings' => $this->toBool($this->input('listings')),
        ]);
    }

    private function toBool($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

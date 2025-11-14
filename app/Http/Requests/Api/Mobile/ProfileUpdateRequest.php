<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                'string',
                'min:6',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'verification_code' => ['nullable', 'digits:6'],
            'timezone' => ['nullable', 'timezone'],
            'language' => ['nullable', Rule::in(['ru', 'en', 'hy'])],
        ];
    }
}

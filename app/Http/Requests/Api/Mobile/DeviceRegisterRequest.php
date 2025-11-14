<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeviceRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:191'],
            'platform' => ['required', Rule::in(['android', 'ios'])],
            'fcm_token' => ['nullable', 'string', 'max:500'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}

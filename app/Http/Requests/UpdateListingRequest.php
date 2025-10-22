<?php
namespace App\Http\Requests;

use App\Rules\RequiredLanguage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('update', $this->route('listing'));
    }

    public function rules(): array
    {
        $maxImages = auth()->user()->role === 'agency' ? 12 : 6;

        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:100',
                new RequiredLanguage(),
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'region_id' => [
                'required',
                'exists:regions,id',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'currency' => [
                'nullable',
                'string',
                'in:USD,AMD,RUB',
            ],
            'description' => [
                'required',
                'string',
                'min:20',
                'max:5000',
                new RequiredLanguage(),
            ],
            'images' => [
                'nullable',
                'array',
                'max:' . $maxImages,
            ],
            'images.*' => [
                'image',
                'mimes:jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок обязателен.',
            'title.min' => 'Заголовок должен содержать минимум 3 символа.',
            'title.max' => 'Заголовок не может превышать 100 символов.',

            'category_id.required' => 'Выберите категорию.',
            'category_id.exists' => 'Выбранная категория не существует.',

            'region_id.required' => 'Выберите регион.',
            'region_id.exists' => 'Выбранный регион не существует.',

            'price.required' => 'Укажите цену.',
            'price.numeric' => 'Цена должна быть числом.',
            'price.min' => 'Цена не может быть отрицательной.',
            'price.max' => 'Цена слишком большая.',

            'description.required' => 'Описание обязательно.',
            'description.min' => 'Описание должно содержать минимум 20 символов.',
            'description.max' => 'Описание не может превышать 5000 символов.',

            'images.array' => 'Изображения должны быть массивом.',
            'images.max' => 'Максимально ' . (auth()->user()->role === 'agency' ? '12' : '6') . ' изображений.',

            'images.*.image' => 'Файл должен быть изображением.',
            'images.*.mimes' => 'Допустимые форматы: JPEG, PNG, WebP.',
            'images.*.max' => 'Размер изображения не может превышать 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => $this->currency ?? 'USD',
        ]);
    }
}

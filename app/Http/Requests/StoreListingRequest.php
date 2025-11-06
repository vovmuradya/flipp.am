<?php

namespace App\Http\Requests;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Rules\RequiredLanguage;
use App\Support\VehicleAttributeOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Listing::class);
    }

    protected function prepareForValidation(): void
    {
        $vehicle = $this->input('vehicle', []);
        if (!is_array($vehicle)) {
            $vehicle = [];
        }

        $listingType = $this->input('listing_type');
        $isVehicle = $listingType === 'vehicle' || $this->boolean('from_auction');

        if ($isVehicle) {
            [$brand, $vehicle] = $this->resolveBrand($vehicle);
            $vehicle = $this->resolveModel($vehicle, $brand);
            $vehicle = $this->normalizeVehicleFields($vehicle);

            $autoTitle = $this->buildAutoTitle($vehicle);
            if ($autoTitle) {
                $this->merge(['title' => $autoTitle]);
            }

            $this->merge(['vehicle' => $vehicle]);
        }
    }

    public function rules(): array
    {
        $maxImages = auth()->user()->role === 'agency' ? 12 : 6;
        $colorKeys = array_keys(VehicleAttributeOptions::colors());
        $vehicleRequired = $this->input('listing_type') === 'vehicle' || $this->boolean('from_auction');
        $brandId = data_get($this->input('vehicle', []), 'brand_id');

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
            'vehicle.brand_id' => [
                $vehicleRequired ? 'required' : 'nullable',
                Rule::exists('car_brands', 'id'),
            ],
            'vehicle.model_id' => [
                $vehicleRequired ? 'required' : 'nullable',
                Rule::exists('car_models', 'id')->where(function ($query) use ($brandId) {
                    if ($brandId) {
                        $query->where('car_brand_id', $brandId);
                    }
                }),
            ],
            'vehicle.year' => [
                $vehicleRequired ? 'required' : 'nullable',
                'integer',
                'between:1980,' . (date('Y') + 1),
            ],
            'vehicle.exterior_color' => [
                $vehicleRequired ? 'required' : 'nullable',
                Rule::in($colorKeys),
            ],
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
            'vehicle.brand_id.required' => 'Выберите марку из списка.',
            'vehicle.brand_id.exists' => 'Марка должна быть выбрана из справочника.',
            'vehicle.model_id.required' => 'Выберите модель из списка.',
            'vehicle.model_id.exists' => 'Модель должна соответствовать выбранной марке.',
            'vehicle.year.required' => 'Выберите год выпуска из предложенного списка.',
            'vehicle.year.between' => 'Год выпуска должен быть в разумных пределах.',
            'vehicle.exterior_color.required' => 'Выберите цвет кузова из списка.',
            'vehicle.exterior_color.in' => 'Цвет кузова должен быть выбран из предложенных вариантов.',
        ];
    }

    private function resolveBrand(array $vehicle): array
    {
        $brand = null;
        $brandId = $vehicle['brand_id'] ?? null;
        $brandName = $vehicle['make'] ?? null;

        if ($brandId) {
            $brand = CarBrand::find($brandId);
        }

        if (!$brand && $brandName) {
            $brand = CarBrand::query()
                ->get()
                ->first(function (CarBrand $candidate) use ($brandName) {
                    $aliases = array_filter([
                        $candidate->name_ru ?? null,
                        $candidate->name_en ?? null,
                    ]);

                    foreach ($aliases as $alias) {
                        if ($this->normalizeText($alias) === $this->normalizeText($brandName)) {
                            return true;
                        }
                    }

                    return false;
                });
        }

        if ($brand) {
            $vehicle['brand_id'] = $brand->id;
            $vehicle['make'] = $brand->name_ru
                ?: ($brand->name_en ?: (string) $brand->id);
        } else {
            $vehicle['brand_id'] = null;
        }

        return [$brand, $vehicle];
    }

    private function resolveModel(array $vehicle, ?CarBrand $brand): array
    {
        $model = null;
        $modelId = $vehicle['model_id'] ?? null;
        $modelName = $vehicle['model'] ?? null;

        if ($modelId && $brand) {
            $model = CarModel::query()
                ->where('car_brand_id', $brand->id)
                ->find($modelId);
        }

        if (!$model && $modelName && $brand) {
            $model = CarModel::query()
                ->where('car_brand_id', $brand->id)
                ->get()
                ->first(function (CarModel $candidate) use ($modelName) {
                    $aliases = array_filter([
                        $candidate->name_ru ?? null,
                        $candidate->name_en ?? null,
                    ]);

                    foreach ($aliases as $alias) {
                        if ($this->normalizeText($alias) === $this->normalizeText($modelName)) {
                            return true;
                        }
                    }

                    return false;
                });
        }

        if ($model) {
            $vehicle['model_id'] = $model->id;
            $vehicle['model'] = $model->name_ru
                ?: ($model->name_en ?: (string) $model->id);
        } else {
            $vehicle['model_id'] = null;
        }

        return $vehicle;
    }

    private function normalizeVehicleFields(array $vehicle): array
    {
        if (!empty($vehicle['year'])) {
            $vehicle['year'] = (int) $vehicle['year'];
        } else {
            $vehicle['year'] = null;
        }

        if (!empty($vehicle['exterior_color'])) {
            $colorKey = $this->matchColorKey($vehicle['exterior_color']);
            $vehicle['exterior_color'] = $colorKey ?: null;
        } else {
            $vehicle['exterior_color'] = null;
        }

        return $vehicle;
    }

    private function buildAutoTitle(array $vehicle): ?string
    {
        $make = $vehicle['make'] ?? null;
        $model = $vehicle['model'] ?? null;

        if (!$make || !$model) {
            return $this->input('title');
        }

        return trim($make . ' ' . $model);
    }

    private function normalizeText(?string $value): string
    {
        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5);
        $normalized = preg_replace('/\s+/u', ' ', trim(mb_strtolower($decoded))) ?: '';

        return $normalized;
    }

    private function matchColorKey(string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        foreach (VehicleAttributeOptions::colors() as $key => $label) {
            if ($normalized === $this->normalizeText($key) || $normalized === $this->normalizeText($label)) {
                return $key;
            }
        }

        return null;
    }
}

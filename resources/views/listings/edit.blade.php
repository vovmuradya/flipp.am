<x-app-layout>
    @php
        use App\Support\VehicleAttributeOptions;

        $bodyTypeOptions = VehicleAttributeOptions::bodyTypes();
        $transmissionOptions = VehicleAttributeOptions::transmissions();
        $fuelTypeOptions = VehicleAttributeOptions::fuelTypes();
        $colorOptions = VehicleAttributeOptions::colors();
        $engineDisplacementOptions = [];
        for ($i = 1; $i <= 100; $i++) {
            $liters = $i / 10;
            $cc = (int) round($liters * 1000);
            $engineDisplacementOptions[] = [
                'cc' => $cc,
                'liters' => $liters,
                'label' => number_format($liters, 1, '.', '') . ' л',
            ];
        }
        $currentYear = (int) date('Y');
        $yearOptions = [];
        for ($year = $currentYear + 1; $year >= 1980; $year--) {
            $yearOptions[] = $year;
        }

        $detail = $listing->vehicleDetail;
        $oldColorKey = old('vehicle.exterior_color');
        $initialColorKey = $oldColorKey !== null ? $oldColorKey : ($detail?->exterior_color ? array_search($detail->exterior_color, $colorOptions) ?: $detail->exterior_color : '');
        if ($initialColorKey && !array_key_exists($initialColorKey, $colorOptions)) {
            $matchKey = null;
            foreach ($colorOptions as $key => $label) {
                if (mb_strtolower($label) === mb_strtolower($initialColorKey)) {
                    $matchKey = $key;
                    break;
                }
            }
            $initialColorKey = $matchKey ?? '';
        }

        $vehiclePrefill = [
            'make' => old('vehicle.make', $detail->make ?? ''),
            'model' => old('vehicle.model', $detail->model ?? ''),
            'year' => old('vehicle.year', $detail->year ?? ''),
            'mileage' => old('vehicle.mileage', $detail->mileage ?? ''),
            'body_type' => old('vehicle.body_type', $detail->body_type ?? ''),
            'transmission' => old('vehicle.transmission', $detail->transmission ?? ''),
            'fuel_type' => old('vehicle.fuel_type', $detail->fuel_type ?? ''),
            'engine_displacement_cc' => (string) old('vehicle.engine_displacement_cc', $detail->engine_displacement_cc ?? ''),
            'exterior_color' => $initialColorKey ?? '',
            'brand_id' => old('vehicle.brand_id', ''),
            'model_id' => old('vehicle.model_id', ''),
            'generation_id' => old('vehicle.generation_id', ''),
        ];

        $titleValue = old('title', $listing->title);
        $descriptionValue = old('description', $listing->description);

        $allCategories = collect($categories ?? []);
        $vehicleCategoryIds = $allCategories->whereIn('slug', ['cars', 'motorcycles', 'trucks'])->pluck('id')->values()->all();
        $partsCategoryIds = $allCategories->where('slug', 'auto-parts')->pluck('id')->values()->all();
        $tiresCategoryIds = $allCategories->where('slug', 'tires')->pluck('id')->values()->all();
        $typeCategoryMap = [
            'vehicle' => $vehicleCategoryIds,
            'parts' => $partsCategoryIds,
            'tires' => $tiresCategoryIds,
        ];

        $initialType = $listing->listing_type ?? 'vehicle';
        $initialCategory = $listing->category_id;

        $listingFormConfig = [
            'initialType' => $initialType,
            'initialCategory' => $initialCategory ? (string) $initialCategory : null,
            'categoryMap' => $typeCategoryMap,
            'isAuction' => false,
            'vehicle' => $vehiclePrefill,
            'locale' => app()->getLocale(),
            'api' => [
                'brands' => url('/api/brands'),
                'models' => url('/api/brands/{brand}/models'),
                'generations' => url('/api/models/{model}/generations'),
            ],
            'colors' => $colorOptions,
            'years' => $yearOptions,
            'initialTitle' => $titleValue,
        ];

        $existingMedia = $listing->getMedia('images');
    @endphp

    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h1 class="brand-section__title">Редактирование объявления</h1>
                <p class="brand-section__subtitle">
                    Обновите данные, замените фотографии и сохраните изменения — карточка обновится моментально.
                </p>
            </div>

            <div class="brand-surface p-0 overflow-hidden">
                <div class="p-6">
                    <form method="POST"
                          action="{{ route('listings.update', $listing) }}"
                          enctype="multipart/form-data"
                          x-data="listingCreateForm(@js($listingFormConfig))"
                          x-init="init()"
                          x-on:submit.prevent="handleSubmit($event)"
                          class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="listing_type" :value="listingType || ''">
                        <input type="hidden" name="category_id" id="category_id" :value="categoryId">

                        <div class="brand-surface mb-4" x-show="listingType === 'vehicle'" x-cloak>
                            <h3 class="h5 mb-3">Характеристики автомобиля</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Марка</label>
                                    <input type="hidden" name="vehicle[brand_id]" :value="vehicle.brandId">
                                    <input type="text"
                                           class="form-control"
                                           name="vehicle[make]"
                                           list="brand-options"
                                           autocomplete="off"
                                           x-model="vehicle.make"
                                           @focus="ensureBrandsLoaded()"
                                           @input="onBrandInput($event)"
                                           @change="onBrandSelected()"
                                           placeholder="Выберите марку из списка"
                                           required>
                                    <datalist id="brand-options"></datalist>
                                    <template x-if="formErrors.brand">
                                        <small class="text-danger d-block mt-1" x-text="formErrors.brand"></small>
                                    </template>
                                    @error('vehicle.brand_id')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Модель</label>
                                    <input type="hidden" name="vehicle[model_id]" :value="vehicle.modelId">
                                    <input type="text"
                                           class="form-control"
                                           name="vehicle[model]"
                                           list="model-options"
                                           autocomplete="off"
                                           x-model="vehicle.model"
                                           @input="onModelInput($event)"
                                           @change="onModelSelected()"
                                           placeholder="Выберите модель"
                                           required>
                                    <datalist id="model-options"></datalist>
                                    <template x-if="formErrors.model">
                                        <small class="text-danger d-block mt-1" x-text="formErrors.model"></small>
                                    </template>
                                    @error('vehicle.model_id')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Год выпуска</label>
                                    <select class="form-select"
                                            name="vehicle[year]"
                                            x-model="vehicle.year"
                                            required>
                                        <option value="">Выберите год</option>
                                        @foreach($yearOptions as $yearOption)
                                            <option value="{{ $yearOption }}" @selected((string) $vehiclePrefill['year'] === (string) $yearOption)>
                                                {{ $yearOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <template x-if="formErrors.year">
                                        <small class="text-danger d-block mt-1" x-text="formErrors.year"></small>
                                    </template>
                                    @error('vehicle.year')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Пробег (км)</label>
                                    <input type="number"
                                           class="form-control"
                                           name="vehicle[mileage]"
                                           min="0"
                                           x-model="vehicle.mileage">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Тип кузова</label>
                                    <select class="form-select" name="vehicle[body_type]">
                                        <option value="">Не указан</option>
                                        @foreach($bodyTypeOptions as $key => $label)
                                            <option value="{{ $key }}" @selected($vehiclePrefill['body_type'] === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Трансмиссия</label>
                                    <select class="form-select" name="vehicle[transmission]">
                                        <option value="">Не указана</option>
                                        @foreach($transmissionOptions as $key => $label)
                                            <option value="{{ $key }}" @selected($vehiclePrefill['transmission'] === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Тип топлива</label>
                                    <select class="form-select" name="vehicle[fuel_type]">
                                        <option value="">Не указано</option>
                                        @foreach($fuelTypeOptions as $key => $label)
                                            <option value="{{ $key }}" @selected($vehiclePrefill['fuel_type'] === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Объём двигателя</label>
                                    <select class="form-select"
                                            name="vehicle[engine_displacement_cc]"
                                            x-model="vehicle.engine_displacement_cc">
                                        <option value="">Не указан</option>
                                        @foreach($engineDisplacementOptions as $option)
                                            <option value="{{ $option['cc'] }}"
                                                {{ (string) $vehiclePrefill['engine_displacement_cc'] === (string) $option['cc'] ? 'selected' : '' }}>
                                                {{ $option['label'] }} ({{ $option['cc'] }} см³)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Цвет кузова</label>
                                    <select class="form-select"
                                            name="vehicle[exterior_color]"
                                            x-model="vehicle.exteriorColor"
                                            required>
                                        <option value="">Выберите цвет</option>
                                        @foreach($colorOptions as $key => $label)
                                            <option value="{{ $key }}" @selected($vehiclePrefill['exterior_color'] === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <template x-if="formErrors.color">
                                        <small class="text-danger d-block mt-1" x-text="formErrors.color"></small>
                                    </template>
                                    @error('vehicle.exterior_color')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Заголовок <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   x-model="titleValue"
                                   :readonly="listingType === 'vehicle'"
                                   required>
                            @error('title')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Описание <span class="text-danger">*</span></label>
                            <textarea name="description" rows="5" class="form-control" required>{{ $descriptionValue }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Цена (AMD) <span class="text-danger">*</span></label>
                            <input type="number" name="price" min="0" value="{{ old('price', $listing->price) }}" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Регион <span class="text-danger">*</span></label>
                            <select name="region_id" class="form-select" required>
                                <option value="">Выберите регион</option>
                                @foreach($regions as $region)
                                    @php
                                        $rraw = $region->name ?? '';
                                        if (is_string($rraw)) {
                                            $rdec = json_decode($rraw, true);
                                            $rlabel = is_array($rdec) ? ($rdec[app()->getLocale()] ?? $rdec['ru'] ?? $rdec['en'] ?? array_values($rdec)[0]) : $rraw;
                                        } elseif (is_array($rraw)) {
                                            $rlabel = $rraw[app()->getLocale()] ?? $rraw['ru'] ?? $rraw['en'] ?? (array_values($rraw)[0] ?? '');
                                        } else {
                                            $rlabel = (string) $rraw;
                                        }
                                    @endphp
                                    <option value="{{ $region->id }}" @selected(old('region_id', $listing->region_id) == $region->id)>{{ $rlabel }}</option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        @if($existingMedia->isNotEmpty())
                            <div class="mb-4">
                                <label class="form-label d-block">Текущие изображения</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($existingMedia as $media)
                                        <div class="position-relative" style="width: 110px; height: 80px; border-radius: 12px; overflow: hidden; background: #f1f3f5;">
                                            <img src="{{ $media->getUrl('thumb') ?: $media->getUrl() }}" alt="media" class="w-100 h-100 object-cover">
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-2">Вы можете добавить новые изображения — они будут добавлены к текущим.</small>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label">Добавить изображения</label>
                            <input type="file" name="images[]" multiple accept="image/*" class="form-control">
                            <small class="text-muted">PNG, JPG, WEBP до 5MB</small>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('dashboard.my-listings') }}" class="btn btn-brand-outline">Отмена</a>
                            <button type="submit" class="btn btn-brand-gradient">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @include('listings.partials.vehicle-form-script')
</x-app-layout>

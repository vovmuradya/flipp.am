@extends('layouts.app')

@section('content')
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-surface p-0 overflow-hidden">
                <div class="brand-form__header {{ $auctionData ? 'brand-form__header--auction' : '' }}">
                    <h1 class="text-2xl font-bold mb-1">
                        {{ $auctionData ? '🚗 Создать объявление с аукциона' : 'Создать объявление' }}
                    </h1>
                    @if($auctionData && isset($auctionData['auction_url']))
                        <p class="mb-0">
                            Источник: <a href="{{ $auctionData['auction_url'] }}" target="_blank">{{ $auctionData['auction_url'] }}</a>
                        </p>
                    @endif
                </div>

                <div class="p-6">
                    @if(request()->has('from_auction') && !$auctionData)
                        <div class="brand-surface mb-4" style="background: rgba(244,140,37,0.08); border-radius: 14px;">
                            <h2 class="text-lg font-semibold mb-3">🔗 Введите ссылку на аукцион</h2>
                            <form id="auctionUrlForm" class="d-flex gap-2 flex-wrap">
                                <input type="url"
                                       id="auctionUrl"
                                       placeholder="https://www.copart.com/lot/..."
                                       class="flex-grow-1 form-control"
                                       required>
                                <button type="button" id="parseBtn" class="btn-brand-gradient">Загрузить</button>
                            </form>
                            <p id="loadingMsg" class="text-muted mt-2 small" hidden>⏳ Загрузка данных...</p>
                            <p id="errorMsg" class="text-danger mt-2 small" hidden></p>
                        </div>
                    @endif

                    @php
                        $ad = $auctionData ?? null;
                        $adVehicleRaw = $ad['vehicle'] ?? [];

                        $adV = is_array($adVehicleRaw) ? $adVehicleRaw : [];
                        if ($ad && empty($adV)) {
                            $adV = [
                                'make' => $ad['make'] ?? null,
                                'model' => $ad['model'] ?? null,
                                'year' => $ad['year'] ?? null,
                                'mileage' => $ad['mileage'] ?? null,
                                'exterior_color' => $ad['exterior_color'] ?? null,
                                'transmission' => $ad['transmission'] ?? null,
                                'fuel_type' => $ad['fuel_type'] ?? null,
                                'engine_displacement_cc' => $ad['engine_displacement_cc'] ?? null,
                                'body_type' => $ad['body_type'] ?? null,
                                'auction_ends_at' => $ad['auction_ends_at'] ?? null,
                            ];
                        }
                        if ($ad && empty(data_get($adV, 'auction_ends_at')) && !empty(data_get($ad, 'auction_ends_at'))) {
                            $adV['auction_ends_at'] = $ad['auction_ends_at'];
                        }

                        $displayPhotos = [];
                        if ($ad) {
                            $rawPhotos = [];
                            if (!empty($ad['photos']) && is_array($ad['photos'])) {
                                $rawPhotos = array_merge($rawPhotos, $ad['photos']);
                            }
                            if (!empty($adVehicleRaw['photos']) && is_array($adVehicleRaw['photos'])) {
                                $rawPhotos = array_merge($rawPhotos, $adVehicleRaw['photos']);
                            }
                            if (!empty($rawPhotos)) {
                                $seenPaths = [];
                                foreach ($rawPhotos as $photo) {
                                    $photoUrl = null;
                                    if (is_string($photo)) {
                                        $photoUrl = trim($photo);
                                    } elseif (is_array($photo)) {
                                        foreach (['url','full','large','src','path'] as $key) {
                                            if (!empty($photo[$key]) && is_string($photo[$key])) {
                                                $photoUrl = trim($photo[$key]);
                                                break;
                                            }
                                        }
                                    }
                                    if (empty($photoUrl)) {
                                        continue;
                                    }
                                    if (stripos($photoUrl, 'No+Image') !== false) {
                                        continue;
                                    }
                                    $realUrl = $photoUrl;
                                    if (str_contains($photoUrl, '/proxy/image') || str_contains($photoUrl, 'image-proxy')) {
                                        $parsed = parse_url($photoUrl);
                                        if (!empty($parsed['query'])) {
                                            parse_str($parsed['query'], $params);
                                            if (!empty($params['u'])) {
                                                $realUrl = urldecode($params['u']);
                                            }
                                        }
                                    }
                                    $path = parse_url($realUrl, PHP_URL_PATH) ?? $realUrl;
                                    $normalizedPath = strtolower(preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path));
                                    if (isset($seenPaths[$normalizedPath])) {
                                        continue;
                                    }
                                    $seenPaths[$normalizedPath] = true;
                                    $displayPhotos[] = $photoUrl;
                                }
                                $displayPhotos = array_slice($displayPhotos, 0, 14);
                            }
                        }
                        $mainImageDefault = $displayPhotos[0] ?? 'https://placehold.co/200x150/e5e7eb/6b7280?text=Нет+фото';

                        $allCategories = collect($categories ?? []);
                        $vehicleCategoryIds = $allCategories->whereIn('slug', ['cars', 'motorcycles', 'trucks'])->pluck('id')->values()->all();
                        $partsCategoryIds = $allCategories->where('slug', 'auto-parts')->pluck('id')->values()->all();
                        $tiresCategoryIds = $allCategories->where('slug', 'tires')->pluck('id')->values()->all();

                        $typeCategoryMap = [
                            'vehicle' => $vehicleCategoryIds,
                            'parts' => $partsCategoryIds,
                            'tires' => $tiresCategoryIds,
                        ];

                        $sectionCards = [
                            'vehicle' => ['title' => 'Автомобили', 'icon' => '🚗'],
                            'parts' => ['title' => 'Запчасти', 'icon' => '🛠️'],
                            'tires' => ['title' => 'Шины', 'icon' => '🛞'],
                        ];

                        $initialType = $ad ? 'vehicle' : (old('section') ?? old('listing_type'));
                        if ($initialType && !array_key_exists($initialType, $sectionCards)) {
                            $initialType = $ad ? 'vehicle' : null;
                        }

                        $initialCategory = old('category_id');
                        if ($ad) {
                            $initialCategory = $initialCategory
                                ?? ($ad['category_id'] ?? ($vehicleCategoryIds[0] ?? null));
                        } elseif (!$initialCategory && !empty($typeCategoryMap[$initialType] ?? [])) {
                            $initialCategory = $typeCategoryMap[$initialType][0];
                        }

                        $vehicleOld = old('vehicle', []);

                        $vehiclePrefill = [
                            'make' => old('vehicle.make', $adV['make'] ?? $ad['make'] ?? ''),
                            'model' => old('vehicle.model', $adV['model'] ?? $ad['model'] ?? ''),
                            'year' => old('vehicle.year', $adV['year'] ?? $ad['year'] ?? ''),
                            'brand_id' => old('vehicle.brand_id'),
                            'model_id' => old('vehicle.model_id'),
                            'generation_id' => old('vehicle.generation_id'),
                        ];

                        $listingFormConfig = [
                            'initialType' => $initialType,
                            'initialCategory' => $initialCategory ? (string) $initialCategory : null,
                            'categoryMap' => $typeCategoryMap,
                            'isAuction' => (bool) $ad,
                            'vehicle' => $vehiclePrefill,
                            'locale' => app()->getLocale(),
                            'api' => [
                                'brands' => url('/api/brands'),
                                'models' => url('/api/brands/{brand}/models'),
                                'generations' => url('/api/models/{model}/generations'),
                            ],
                        ];
                    @endphp

                    @if($ad && !empty($displayPhotos))
                        <div class="mb-4">
                            <h3 class="text-lg fw-semibold mb-3">📸 Фотографии с аукциона ({{ count($displayPhotos) }})</h3>
                            <div x-data="{ mainImage: '{{ addslashes($mainImageDefault) }}' }">
                                <div class="mx-auto mb-3" style="width: 220px; height: 165px; border-radius: 14px; overflow: hidden; background: #f1f3f5;">
                                    <img :src="mainImage" alt="Главное фото"
                                         style="width: 100%; height: 100%; object-fit: contain;"
                                         onerror="this.src='https://placehold.co/200x150/e5e7eb/6b7280?text=Нет+фото'">
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($displayPhotos as $index => $photoUrl)
                                        <img src="{{ $photoUrl }}" alt="Фото {{ $index + 1 }}" width="70" height="70"
                                             style="border-radius: 10px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb;"
                                             @click="mainImage = '{{ addslashes($photoUrl) }}'"
                                             onerror="this.style.display='none'">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="fw-semibold mb-2">Исправьте следующие ошибки:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" x-data="listingCreateForm(@js($listingFormConfig))" x-init="init()" class="space-y-6">
                        @csrf

                        @unless($ad)
                            <input type="hidden" name="section" :value="selectedType || ''">
                            <input type="hidden" name="listing_type" :value="listingType || ''">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Выберите раздел</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($sectionCards as $typeKey => $card)
                                        <button type="button"
                                                class="btn btn-light border rounded-3 d-flex align-items-center gap-2 px-3 py-2"
                                                :class="selectedType === '{{ $typeKey }}' ? 'border-danger bg-danger-subtle text-danger fw-semibold' : ''"
                                                @click="setType('{{ $typeKey }}')">
                                            <span>{{ $card['icon'] }}</span>
                                            <span>{{ $card['title'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endunless

                        @if($ad)
                            <input type="hidden" name="from_auction" value="1">
                            <input type="hidden" name="listing_type" value="vehicle">
                            <input type="hidden" name="vehicle[is_from_auction]" value="1">
                            <input type="hidden" name="vehicle[source_auction_url]" value="{{ $ad['auction_url'] ?? '' }}">
                            @if(!empty($adV['auction_ends_at']))
                                <input type="hidden" name="vehicle[auction_ends_at]" value="{{ $adV['auction_ends_at'] }}">
                            @endif
                            <input type="hidden" name="category_id" value="1">
                            @foreach($displayPhotos as $photo)
                                <input type="hidden" name="auction_photos[]" value="{{ $photo }}">
                            @endforeach
                        @endif

                        @php
                            $titleValue = old('title', $ad['title'] ?? '');
                            $descriptionValue = old('description', $ad['description'] ?? '');
                        @endphp

                        @if($ad)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Заголовок</label>
                                <div class="form-control-plaintext text-body">{{ $titleValue }}</div>
                                <input type="hidden" name="title" value="{{ $titleValue }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Описание</label>
                                <div class="border rounded-3 bg-light p-3 text-body" style="white-space: pre-line;">
                                    {{ $descriptionValue }}
                                </div>
                                <textarea name="description" hidden>{{ $descriptionValue }}</textarea>
                            </div>
                        @else
                            <div>
                                <label class="form-label">Заголовок <span class="text-danger">*</span></label>
                                <input type="text" name="title" value="{{ $titleValue }}" class="form-control" required>
                            </div>

                            <div>
                                <label class="form-label">Описание <span class="text-danger">*</span></label>
                                <textarea name="description" rows="5" class="form-control" required>{{ $descriptionValue }}</textarea>
                            </div>
                        @endif

                        <div>
                            <label class="form-label">Цена (AMD) <span class="text-danger">*</span></label>
                            <input type="number" name="price" min="0" value="{{ old('price', $ad['price'] ?? '') }}" class="form-control" required>
                        </div>

                        @if(! $ad)
                            <div>
                                <label class="form-label">Категория <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select" x-model="categoryId" required>
                                    <option value="">Выберите категорию</option>
                                    @foreach($categories as $category)
                                        @php
                                            $sectionsForCategory = [];
                                            foreach ($typeCategoryMap as $sectionKey => $ids) {
                                                if (in_array($category->id, $ids, true)) {
                                                    $sectionsForCategory[] = $sectionKey;
                                                }
                                            }
                                            if (empty($sectionsForCategory)) {
                                                $sectionsForCategory[] = 'all';
                                            }
                                        @endphp
                                        <option value="{{ $category->id }}"
                                                data-sections="{{ implode(',', $sectionsForCategory) }}"
                                                {{ old('category_id', $ad['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->localized_name ?? $category->name ?? 'Категория' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($ad)
                            @php
                                $vehicleHiddenValues = [
                                    'make' => data_get($vehicleOld, 'make', $adV['make'] ?? $ad['make'] ?? ''),
                                    'model' => data_get($vehicleOld, 'model', $adV['model'] ?? $ad['model'] ?? ''),
                                    'year' => data_get($vehicleOld, 'year', $adV['year'] ?? $ad['year'] ?? ''),
                                    'mileage' => data_get($vehicleOld, 'mileage', $adV['mileage'] ?? $ad['mileage'] ?? ''),
                                    'body_type' => data_get($vehicleOld, 'body_type', $adV['body_type'] ?? $ad['body_type'] ?? ''),
                                    'transmission' => data_get($vehicleOld, 'transmission', $adV['transmission'] ?? $ad['transmission'] ?? ''),
                                    'fuel_type' => data_get($vehicleOld, 'fuel_type', $adV['fuel_type'] ?? $ad['fuel_type'] ?? ''),
                                    'engine_displacement_cc' => data_get($vehicleOld, 'engine_displacement_cc', $adV['engine_displacement_cc'] ?? $ad['engine_displacement_cc'] ?? ''),
                                    'exterior_color' => data_get($vehicleOld, 'exterior_color', $adV['exterior_color'] ?? $ad['exterior_color'] ?? ''),
                                ];

                                $vehicleDisplayPairs = [
                                    'Марка' => $vehicleHiddenValues['make'],
                                    'Модель' => $vehicleHiddenValues['model'],
                                    'Год выпуска' => $vehicleHiddenValues['year'],
                                    'Пробег' => $vehicleHiddenValues['mileage'] ? number_format((int) $vehicleHiddenValues['mileage'], 0, '.', ' ') . ' км' : null,
                                    'Тип кузова' => $vehicleHiddenValues['body_type'],
                                    'Трансмиссия' => $vehicleHiddenValues['transmission'],
                                    'Топливо' => $vehicleHiddenValues['fuel_type'],
                                    'Объём двигателя' => $vehicleHiddenValues['engine_displacement_cc'] ? number_format((int) $vehicleHiddenValues['engine_displacement_cc'], 0, '.', ' ') . ' см³' : null,
                                    'Цвет' => $vehicleHiddenValues['exterior_color'],
                                ];
                            @endphp

                            <div class="brand-surface">
                                <h3 class="h5 mb-3">Характеристики автомобиля</h3>
                                <div class="row g-3">
                                    @foreach($vehicleDisplayPairs as $label => $value)
                                        @if(!empty($value))
                                            <div class="col-md-6">
                                                <span class="text-muted d-block small">{{ $label }}</span>
                                                <strong class="text-body">{{ $value }}</strong>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            @foreach($vehicleHiddenValues as $key => $value)
                                <input type="hidden" name="vehicle[{{ $key }}]" value="{{ $value }}">
                            @endforeach
                        @else
                            <div class="brand-surface mb-4" id="vehicle-fields" x-show="currentStep === 1" x-cloak>
                                <h3 class="h5 mb-3">Характеристики автомобиля</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Марка</label>
                                        <select class="form-select mb-2"
                                                x-model="vehicle.brandId"
                                                @change="handleBrandChange"
                                                :disabled="vehicle.loadingBrands && vehicle.brands.length === 0">
                                            <option value="">Выберите марку</option>
                                            <template x-for="brand in vehicle.brands" :key="brand.id">
                                                <option :value="String(brand.id)" x-text="brandLabel(brand)"></option>
                                            </template>
                                        </select>
                                        <template x-if="vehicle.loadingBrands">
                                            <small class="text-muted d-block mb-1">Загружаем список марок…</small>
                                        </template>
                                        <input type="hidden" name="vehicle[brand_id]" :value="vehicle.brandId">
                                        <small class="text-muted d-block mb-1">Выберите марку из списка или введите вручную.</small>
                                        <input type="text"
                                               name="vehicle[make]"
                                               class="form-control"
                                               value="{{ old('vehicle.make', $adV['make'] ?? $ad['make'] ?? '') }}"
                                               x-model="vehicle.make"
                                               placeholder="Например, Toyota">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Модель</label>
                                        <select class="form-select mb-2"
                                                x-model="vehicle.modelId"
                                                @change="handleModelChange"
                                                :disabled="!vehicle.brandId || vehicle.loadingModels || vehicle.models.length === 0">
                                            <option value="">Выберите модель</option>
                                            <template x-for="model in vehicle.models" :key="model.id">
                                                <option :value="String(model.id)" x-text="modelLabel(model)"></option>
                                            </template>
                                        </select>
                                        <template x-if="vehicle.loadingModels">
                                            <small class="text-muted d-block mb-1">Загружаем модели…</small>
                                        </template>
                                        <input type="hidden" name="vehicle[model_id]" :value="vehicle.modelId">
                                        <input type="text"
                                               name="vehicle[model]"
                                               class="form-control"
                                               value="{{ old('vehicle.model', $adV['model'] ?? $ad['model'] ?? '') }}"
                                               x-model="vehicle.model"
                                               placeholder="Например, Camry">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Год выпуска</label>
                                        <input type="number"
                                               name="vehicle[year]"
                                               min="1900"
                                               max="{{ date('Y') + 1 }}"
                                               class="form-control"
                                               value="{{ old('vehicle.year', $adV['year'] ?? $ad['year'] ?? '') }}"
                                               x-model="vehicle.year">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Поколение</label>
                                        <select class="form-select"
                                                x-model="vehicle.generationId"
                                                @change="handleGenerationChange"
                                                :disabled="!vehicle.modelId || vehicle.loadingGenerations || vehicle.generations.length === 0">
                                            <option value="">Выберите поколение</option>
                                            <template x-for="generation in vehicle.generations" :key="generation.id">
                                                <option :value="String(generation.id)" x-text="generationLabel(generation)"></option>
                                            </template>
                                        </select>
                                        <template x-if="vehicle.loadingGenerations">
                                            <small class="text-muted d-block mt-1">Загружаем поколения…</small>
                                        </template>
                                        <input type="hidden" name="vehicle[generation_id]" :value="vehicle.generationId">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Пробег (км)</label>
                                        <input type="number" name="vehicle[mileage]" min="0" value="{{ old('vehicle.mileage', $adV['mileage'] ?? $ad['mileage'] ?? '') }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Тип кузова</label>
                                        <select name="vehicle[body_type]" class="form-select">
                                            <option value="">Выберите</option>
                                            @php
                                                $bodyTypes = [
                                                    'sedan' => 'Седан',
                                                    'suv' => 'SUV / Внедорожник',
                                                    'coupe' => 'Купе',
                                                    'hatchback' => 'Хэтчбек',
                                                    'wagon' => 'Универсал',
                                                    'pickup' => 'Пикап',
                                                    'minivan' => 'Минивэн',
                                                    'convertible' => 'Кабриолет',
                                                ];
                                                $selectedBody = old('vehicle.body_type', $adV['body_type'] ?? $ad['body_type'] ?? '');
                                            @endphp
                                            @foreach($bodyTypes as $value => $label)
                                                <option value="{{ $value }}" {{ $selectedBody === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Трансмиссия</label>
                                        <select name="vehicle[transmission]" class="form-select">
                                            <option value="">Выберите</option>
                                            @php
                                                $transmissions = ['automatic' => 'Автомат', 'manual' => 'Механика', 'cvt' => 'CVT', 'semi-automatic' => 'Робот'];
                                                $selectedTransmission = old('vehicle.transmission', $adV['transmission'] ?? $ad['transmission'] ?? '');
                                            @endphp
                                            @foreach($transmissions as $value => $label)
                                                <option value="{{ $value }}" {{ $selectedTransmission === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Топливо</label>
                                        <select name="vehicle[fuel_type]" class="form-select">
                                            <option value="">Выберите</option>
                                            @php
                                                $fuelTypes = ['gasoline' => 'Бензин','diesel' => 'Дизель','hybrid' => 'Гибрид','electric' => 'Электро','lpg' => 'Газ'];
                                                $selectedFuelType = old('vehicle.fuel_type', $adV['fuel_type'] ?? $ad['fuel_type'] ?? '');
                                            @endphp
                                            @foreach($fuelTypes as $value => $label)
                                                <option value="{{ $value }}" {{ $selectedFuelType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Объём двигателя (см³)</label>
                                        <input type="number" name="vehicle[engine_displacement_cc]" min="0" value="{{ old('vehicle.engine_displacement_cc', $adV['engine_displacement_cc'] ?? $ad['engine_displacement_cc'] ?? '') }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Цвет кузова</label>
                                        <input type="text" name="vehicle[exterior_color]" value="{{ old('vehicle.exterior_color', $adV['exterior_color'] ?? $ad['exterior_color'] ?? '') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(! $ad)
                            <div>
                                <label class="form-label">Регион <span class="text-danger">*</span></label>
                                <select name="region_id" id="region_id" class="form-select" required>
                                    <option value="">Выберите регион</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id')
                                <p class="text-danger small mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if(! $ad)
                            <div>
                                <label class="form-label">Изображения</label>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control">
                                <small class="text-muted">PNG, JPG, WEBP до 5MB</small>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-3 pt-3">
                            <a href="{{ route('home') }}" class="btn-brand-outline">Отмена</a>
                            <button type="submit" class="btn-brand-gradient" :disabled="!formVisible">
                                {{ $ad ? '🚀 Создать объявление с аукциона' : 'Создать объявление' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('listingCreateForm', (config) => ({
                selectedType: config.initialType ?? null,
                categoryId: config.initialCategory || '',
                categoryMap: config.categoryMap || {},
                isAuction: Boolean(config.isAuction),
                locale: config.locale || 'ru',
                apiEndpoints: config.api || {},
                vehicle: {
                    make: config.vehicle?.make || '',
                    model: config.vehicle?.model || '',
                    year: config.vehicle?.year || '',
                    brandId: config.vehicle?.brand_id ? String(config.vehicle.brand_id) : '',
                    modelId: config.vehicle?.model_id ? String(config.vehicle.model_id) : '',
                    generationId: config.vehicle?.generation_id ? String(config.vehicle.generation_id) : '',
                    brands: [],
                    models: [],
                    generations: [],
                    loadingBrands: false,
                    loadingModels: false,
                    loadingGenerations: false,
                },
                get listingType() {
                    if (this.selectedType === 'vehicle') return 'vehicle';
                    if (this.selectedType === 'parts' || this.selectedType === 'tires') return 'parts';
                    return '';
                },
                get formVisible() {
                    return this.isAuction || Boolean(this.selectedType);
                },
                async init() {
                    if (this.isAuction && !this.selectedType) {
                        this.selectedType = 'vehicle';
                    }
                    if (this.formVisible) {
                        this.ensureCategoryInScope();
                        this.syncCategoryOptions();
                    }
                    await this.initializeVehicleFormIfNeeded();

                    this.$watch('selectedType', async (value) => {
                        if (!value) {
                            this.categoryId = '';
                            this.syncCategoryOptions();
                            return;
                        }
                        this.ensureCategoryInScope();
                        this.syncCategoryOptions();
                        if (value === 'vehicle') {
                            await this.initializeVehicleFormIfNeeded(true);
                        }
                    });

                    this.$watch('categoryId', () => this.syncCategoryOptions());
                },
                setType(type) {
                    if (this.selectedType === type) {
                        this.ensureCategoryInScope();
                        this.syncCategoryOptions();
                        return;
                    }
                    this.selectedType = type;
                },
                ensureCategoryInScope() {
                    if (!this.formVisible) return;
                    const allowed = this.categoryMap[this.selectedType] || [];
                    if (!Array.isArray(allowed) || allowed.length === 0) {
                        if (!this.categoryId) {
                            const first = this.findFirstOptionValue();
                            if (first) this.categoryId = first;
                        }
                        return;
                    }
                    const numericCurrent = Number(this.categoryId);
                    if (!allowed.includes(numericCurrent)) {
                        this.categoryId = String(allowed[0]);
                    }
                },
                syncCategoryOptions() {
                    if (this.isAuction) return;
                    const select = document.getElementById('category_id');
                    if (!select) return;
                    const allowed = this.categoryMap[this.selectedType] || [];
                    const allowedSet = new Set(allowed);
                    const limitByType = Boolean(this.selectedType) && Array.isArray(allowed) && allowed.length > 0;
                    Array.from(select.options).forEach((option) => {
                        if (!option.value) { option.hidden = false; return; }
                        const sections = option.dataset.sections ? option.dataset.sections.split(',') : [];
                        if (sections.includes('all')) { option.hidden = false; return; }
                        if (!limitByType) { option.hidden = false; return; }
                        const optionId = Number(option.value);
                        const shouldShow = allowedSet.has(optionId);
                        option.hidden = !shouldShow;
                        if (!shouldShow && option.selected) option.selected = false;
                    });
                },
                findFirstOptionValue() {
                    const select = document.getElementById('category_id');
                    if (!select) return null;
                    const option = Array.from(select.options).find(opt => opt.value);
                    return option ? String(option.value) : null;
                },
                async initializeVehicleFormIfNeeded(force = false) {
                    if (this.isAuction) {
                        return;
                    }
                    if (!force && this.selectedType !== 'vehicle') {
                        return;
                    }
                    await this.ensureBrandsLoaded();
                    if (this.vehicle.brands.length === 0) {
                        return;
                    }
                    if (!this.vehicle.brandId) {
                        this.syncBrandFromName();
                    }
                    if (this.vehicle.brandId) {
                        await this.fetchModels(this.vehicle.brandId, { preserveSelection: true });
                    }
                    if (this.vehicle.modelId) {
                        await this.fetchGenerations(this.vehicle.modelId, { preserveSelection: true });
                    }
                },
                async ensureBrandsLoaded() {
                    if (this.isAuction || this.vehicle.loadingBrands || this.vehicle.brands.length > 0) {
                        return;
                    }
                    if (!this.apiEndpoints.brands) {
                        return;
                    }
                    this.vehicle.loadingBrands = true;
                    try {
                        const response = await fetch(this.apiEndpoints.brands);
                        if (!response.ok) {
                            throw new Error('Failed to load brands');
                        }
                        this.vehicle.brands = await response.json();
                    } catch (error) {
                        console.error('Error loading brands:', error);
                        this.vehicle.brands = [];
                    } finally {
                        this.vehicle.loadingBrands = false;
                    }
                },
                async fetchModels(brandId, { preserveSelection = false } = {}) {
                    if (!brandId || !this.apiEndpoints.models) {
                        this.vehicle.models = [];
                        this.vehicle.modelId = '';
                        return;
                    }
                    this.vehicle.loadingModels = true;
                    try {
                        const url = this.apiUrl('models', { brand: brandId });
                        const response = await fetch(url);
                        if (!response.ok) {
                            throw new Error('Failed to load models');
                        }
                        this.vehicle.models = await response.json();
                        if (preserveSelection) {
                            const exists = this.vehicle.models.some(model => String(model.id) === String(this.vehicle.modelId));
                            if (!exists) {
                                this.vehicle.modelId = '';
                            }
                        } else {
                            this.vehicle.modelId = '';
                        }
                        if (this.vehicle.modelId) {
                            const model = this.vehicle.models.find(m => String(m.id) === String(this.vehicle.modelId));
                            if (model) {
                                this.vehicle.model = this.modelLabel(model);
                            }
                        } else {
                            this.syncModelFromName();
                        }
                    } catch (error) {
                        console.error('Error loading models:', error);
                        this.vehicle.models = [];
                        this.vehicle.modelId = '';
                    } finally {
                        this.vehicle.loadingModels = false;
                    }
                },
                async fetchGenerations(modelId, { preserveSelection = false } = {}) {
                    if (!modelId || !this.apiEndpoints.generations) {
                        this.vehicle.generations = [];
                        this.vehicle.generationId = '';
                        return;
                    }
                    this.vehicle.loadingGenerations = true;
                    try {
                        const url = this.apiUrl('generations', { model: modelId });
                        const response = await fetch(url);
                        if (!response.ok) {
                            throw new Error('Failed to load generations');
                        }
                        this.vehicle.generations = await response.json();
                        if (preserveSelection) {
                            const exists = this.vehicle.generations.some(gen => String(gen.id) === String(this.vehicle.generationId));
                            if (!exists) {
                                this.vehicle.generationId = '';
                            }
                        } else {
                            this.vehicle.generationId = '';
                        }
                    } catch (error) {
                        console.error('Error loading generations:', error);
                        this.vehicle.generations = [];
                        this.vehicle.generationId = '';
                    } finally {
                        this.vehicle.loadingGenerations = false;
                    }
                },
                apiUrl(key, replacements = {}) {
                    let template = this.apiEndpoints?.[key] || '';
                    Object.entries(replacements).forEach(([token, value]) => {
                        template = template.replace(`{${token}}`, encodeURIComponent(String(value)));
                    });
                    return template;
                },
                brandLabel(brand) {
                    if (!brand) return '';
                    return brand.name_ru || brand.name || brand.name_en || '';
                },
                modelLabel(model) {
                    if (!model) return '';
                    return model.name_ru || model.name || model.name_en || '';
                },
                generationLabel(generation) {
                    if (!generation) return '';
                    const start = generation.year_start || generation.year_begin || '';
                    const end = generation.year_end || generation.year_finish || '';
                    const years = start && end ? `${start}–${end}` : (start || end || '');
                    const title = generation.name || 'Поколение';
                    return years ? `${title} (${years})` : title;
                },
                normalizeValue(value) {
                    return (value || '').toString().trim().toLowerCase();
                },
                syncBrandFromName() {
                    if (!this.vehicle.make) {
                        return;
                    }
                    const target = this.normalizeValue(this.vehicle.make);
                    const match = this.vehicle.brands.find(brand => this.normalizeValue(this.brandLabel(brand)) === target);
                    if (match) {
                        this.vehicle.brandId = String(match.id);
                        this.vehicle.make = this.brandLabel(match);
                    }
                },
                syncModelFromName() {
                    if (!this.vehicle.model || this.vehicle.models.length === 0) {
                        return;
                    }
                    const target = this.normalizeValue(this.vehicle.model);
                    const match = this.vehicle.models.find(model => this.normalizeValue(this.modelLabel(model)) === target);
                    if (match) {
                        this.vehicle.modelId = String(match.id);
                        this.vehicle.model = this.modelLabel(match);
                    }
                },
                async handleBrandChange() {
                    const brand = this.vehicle.brands.find(item => String(item.id) === String(this.vehicle.brandId));
                    if (brand) {
                        this.vehicle.make = this.brandLabel(brand);
                    }
                    await this.fetchModels(this.vehicle.brandId);
                    this.vehicle.model = '';
                    this.vehicle.generations = [];
                    this.vehicle.generationId = '';
                },
                async handleModelChange() {
                    const model = this.vehicle.models.find(item => String(item.id) === String(this.vehicle.modelId));
                    if (model) {
                        this.vehicle.model = this.modelLabel(model);
                    }
                    await this.fetchGenerations(this.vehicle.modelId);
                    this.vehicle.generationId = '';
                },
                handleGenerationChange() {
                    const generation = this.vehicle.generations.find(item => String(item.id) === String(this.vehicle.generationId));
                    if (generation) {
                        const start = generation.year_start || generation.year_begin;
                        if (!this.vehicle.year && start) {
                            this.vehicle.year = String(start);
                        }
                    }
                },
            }));
        });
    </script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-surface p-0 overflow-hidden">
                @php
                    $isAuctionFlow = (bool) ($fromAuctionFlow ?? request()->boolean('from_auction'));
                    $auctionEndsAtIso = null;
                    $auctionEndsAtDisplay = null;

                    if (!empty($auctionData)) {
                        $rawEndsAt = data_get($auctionData, 'auction_ends_at') ?? data_get($auctionData, 'vehicle.auction_ends_at');
                        if (!empty($rawEndsAt)) {
                            try {
                                $auctionEndsAtCarbon = \Illuminate\Support\Carbon::parse($rawEndsAt)
                                    ->timezone(config('app.timezone'));
                                $auctionEndsAtIso = $auctionEndsAtCarbon->toIso8601String();
                                $auctionEndsAtDisplay = $auctionEndsAtCarbon->translatedFormat('d F Y H:i');
                            } catch (\Throwable $e) {
                                $auctionEndsAtIso = null;
                                $auctionEndsAtDisplay = null;
                            }
                        }
                    }
                @endphp
                <div class="brand-form__header {{ $auctionData ? 'brand-form__header--auction' : '' }}">
                    <h1 class="text-2xl font-bold mb-1">
                        {{ $auctionData ? __('🚗 Создать объявление с аукциона') : __('Создать объявление') }}
                    </h1>
                    @if($auctionData && isset($auctionData['auction_url']))
                        <p class="mb-0">
                            {{ __('Источник:') }} <a href="{{ $auctionData['auction_url'] }}" target="_blank">{{ $auctionData['auction_url'] }}</a>
                        </p>
                    @endif
                </div>

                @if($auctionData && $auctionEndsAtIso)
                    <div class="alert alert-warning mb-0 rounded-0 border-top auction-countdown-banner"
                         role="alert"
                         data-countdown
                         data-expires="{{ $auctionEndsAtIso }}"
                         data-prefix="{{ __('Осталось') }}"
                         data-expired-text="{{ __('Аукцион завершён') }}"
                         data-day-label="{{ __('д') }}">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold text-uppercase small text-muted mb-1">
                                    {{ __('Аукцион завершается') }}
                                </div>
                                <div class="fs-5 fw-semibold" data-countdown-text>{{ __('Загрузка…') }}</div>
                                @if($auctionEndsAtDisplay)
                                    <div class="small text-muted mt-1">
                                        {{ __('Ожидаемая дата: :date (:tz)', ['date' => $auctionEndsAtDisplay, 'tz' => config('app.timezone')]) }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center countdown-units" data-countdown-units>
                                <div class="text-center px-3 py-2 bg-white rounded-3 shadow-sm border border-warning-subtle">
                                    <div class="fs-4 fw-bold" data-countdown-unit="days">00</div>
                                    <div class="text-uppercase small text-muted">{{ __('дни') }}</div>
                                </div>
                                <div class="text-center px-3 py-2 bg-white rounded-3 shadow-sm border border-warning-subtle">
                                    <div class="fs-4 fw-bold" data-countdown-unit="hours">00</div>
                                    <div class="text-uppercase small text-muted">{{ __('часы') }}</div>
                                </div>
                                <div class="text-center px-3 py-2 bg-white rounded-3 shadow-sm border border-warning-subtle">
                                    <div class="fs-4 fw-bold" data-countdown-unit="minutes">00</div>
                                    <div class="text-uppercase small text-muted">{{ __('минуты') }}</div>
                                </div>
                                <div class="text-center px-3 py-2 bg-white rounded-3 shadow-sm border border-warning-subtle">
                                    <div class="fs-4 fw-bold" data-countdown-unit="seconds">00</div>
                                    <div class="text-uppercase small text-muted">{{ __('секунды') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="p-6">
                    @if($isAuctionFlow && !$auctionData)
                        <div class="brand-surface mb-4" style="background: rgba(244,140,37,0.08); border-radius: 14px;">
                            <h2 class="text-lg font-semibold mb-3">{{ __('🔗 Введите ссылку на аукцион') }}</h2>
                            <form id="auctionUrlForm" class="d-flex gap-2 flex-wrap" method="POST" action="{{ route('listings.import-auction') }}">
                                @csrf
                                <input type="url"
                                       id="auctionUrl"
                                       name="auction_url"
                                       placeholder="https://www.copart.com/lot/..."
                                       class="flex-grow-1 form-control"
                                       value="{{ old('auction_url') }}"
                                       required>
                                <button type="submit" id="parseBtn" class="btn-brand-gradient">
                                    {{ __('Загрузить') }}
                                </button>
                            </form>
                            @error('auction_url')
                                <p class="text-danger mt-2 small">{{ $message }}</p>
                            @enderror
                            @if(session('auction_error'))
                                <p class="text-danger mt-2 small">{{ session('auction_error') }}</p>
                            @endif
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

                        $bodyTypeOptions = \App\Support\VehicleAttributeOptions::bodyTypes();
                        $transmissionOptions = \App\Support\VehicleAttributeOptions::transmissions();
                        $fuelTypeOptions = \App\Support\VehicleAttributeOptions::fuelTypes();
                        $colorOptions = \App\Support\VehicleAttributeOptions::colors();
                        $engineDisplacementOptions = [];
                        for ($i = 1; $i <= 100; $i++) {
                            $liters = $i / 10;
                            $cc = (int) round($liters * 1000);
                            $engineDisplacementOptions[] = [
                                'cc' => $cc,
                                'liters' => $liters,
                                'label' => number_format($liters, 1, '.', '') . ' ' . __('л'),
                            ];
                        }
                        $currentYear = (int) date('Y');
                        $yearOptions = [];
                        for ($year = $currentYear + 1; $year >= 1980; $year--) {
                            $yearOptions[] = $year;
                        }

                        $rawBodyType = $adV['body_type'] ?? $ad['body_type'] ?? null;
                        $normalizedBodyType = null;
                        if (is_string($rawBodyType) && trim($rawBodyType) !== '') {
                            $bodyTypeCandidate = mb_strtolower(trim($rawBodyType));
                            $bodyTypeCandidate = str_replace(['-', '_'], ' ', $bodyTypeCandidate);
                            $bodyTypeCandidate = preg_replace('/\s+/', ' ', $bodyTypeCandidate);
                            $bodyTypePatterns = [
                                'sedan' => ['sedan', 'saloon', '4 door', 'седан'],
                                'suv' => ['suv', 'sport utility', 'utility', 'crossover', 'кроссовер', 'внедорож'],
                                'coupe' => ['coupe', 'купе', '2 door', '2dr'],
                                'hatchback' => ['hatch', 'хэтч', 'liftback', 'sportback'],
                                'wagon' => ['wagon', 'универсал', 'estate', 'touring'],
                                'pickup' => ['pickup', 'pick up', 'truck', 'пикап', 'crew cab', 'cab'],
                                'minivan' => ['minivan', 'минивэн', 'mini van', 'passenger van', 'van'],
                                'convertible' => ['convertible', 'cabrio', 'cabriolet', 'roadster', 'кабриолет', 'spider'],
                            ];
                            foreach ($bodyTypePatterns as $key => $patterns) {
                                foreach ($patterns as $pattern) {
                                    if ($pattern === '') {
                                        continue;
                                    }
                                    if (mb_stripos($bodyTypeCandidate, $pattern) !== false) {
                                        $normalizedBodyType = $key;
                                        break 2;
                                    }
                                }
                            }
                            if (!$normalizedBodyType && isset($bodyTypeOptions[$bodyTypeCandidate])) {
                                $normalizedBodyType = $bodyTypeCandidate;
                            }
                        }
                        $auctionBodyTypeLabel = '—';
                        if ($normalizedBodyType) {
                            $auctionBodyTypeLabel = $bodyTypeOptions[$normalizedBodyType] ?? ucfirst($normalizedBodyType);
                            $adV['body_type'] = $normalizedBodyType;
                        } elseif (is_string($rawBodyType) && trim($rawBodyType) !== '') {
                            $auctionBodyTypeLabel = $rawBodyType;
                            $adV['body_type'] = $rawBodyType;
                        }

                        $rawColor = $adV['exterior_color'] ?? $ad['exterior_color'] ?? null;
                        $rawColorDisplay = is_string($rawColor) ? trim($rawColor) : '';
                        $normalizedColor = null;
                        if (is_string($rawColor) && trim($rawColor) !== '') {
                            $rawColorNormalized = mb_strtolower(trim($rawColor));
                            foreach ($colorOptions as $key => $label) {
                                if ($rawColorNormalized === mb_strtolower($key) || $rawColorNormalized === mb_strtolower($label)) {
                                    $normalizedColor = $key;
                                    break;
                                }
                            }
                            if (!$normalizedColor) {
                                $normalizedColor = 'other';
                            }
                        }
                        $displayColor = '';
                        if ($normalizedColor) {
                            $displayColor = $colorOptions[$normalizedColor] ?? ucfirst($normalizedColor);
                            if ($normalizedColor === 'other' && $rawColorDisplay !== '') {
                                $displayColor = $rawColorDisplay;
                            }
                        } elseif ($rawColorDisplay !== '') {
                            $displayColor = $rawColorDisplay;
                        }
                        $adV['exterior_color'] = $normalizedColor ?? '';
                        $adV['exterior_color_display'] = $displayColor;

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
                                    // Извлекаем «реальный» URL для нормализации, если это уже прокси
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
                                    $query = parse_url($realUrl, PHP_URL_QUERY);
                                    $normalizedPath = strtolower(preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path));
                                    $dedupeKey = $normalizedPath . ($query ? '?' . $query : '');
                                    if (isset($seenPaths[$dedupeKey])) {
                                        continue;
                                    }
                                    $seenPaths[$dedupeKey] = true;
                                    // Кладём исходный (пока сырой) URL — далее унифицируем через наш прокси
                                    $displayPhotos[] = $realUrl;
                                }
                                $displayPhotos = array_slice($displayPhotos, 0, 14);
                            }
                        }

                        // ✅ Всегда используем наш прокси + реферер лота
                        $finalPhotos = [];
                        if (!empty($displayPhotos)) {
                            $auctionRef = $ad['auction_url'] ?? ($ad['source_auction_url'] ?? 'https://www.copart.com/');
                            foreach ($displayPhotos as $upstream) {
                                // Если вдруг здесь остался прокси-URL, извлечём исходный u
                                if (str_contains($upstream, '/proxy/image') || str_contains($upstream, 'image-proxy')) {
                                    $p = parse_url($upstream);
                                    if (!empty($p['query'])) {
                                        parse_str($p['query'], $q); $upstream = $q['u'] ?? $upstream;
                                        if (is_string($upstream)) { $upstream = urldecode($upstream); }
                                    }
                                }
                                $proxyBase = route('proxy.image', [], false);
                                $finalPhotos[] = $proxyBase . '?u=' . rawurlencode($upstream) . ($auctionRef ? ('&r=' . rawurlencode($auctionRef)) : '');
                            }
                        }
                        $noPhotoPlaceholder = rawurlencode(__('Нет фото'));
                        $placeholderUrl = "https://placehold.co/200x150/e5e7eb/6b7280?text={$noPhotoPlaceholder}";
                        $mainImageDefault = ($finalPhotos[0] ?? $placeholderUrl);

                        $allCategories = collect($categories ?? []);
                        $vehicleCategoryIds = $allCategories
                            ->whereIn('slug', ['cars', 'motorcycles', 'trucks'])
                            ->pluck('id')
                            ->values()
                            ->all();

                        $typeCategoryMap = [
                            'vehicle' => $vehicleCategoryIds,
                        ];

                        $sectionCards = [
                            'vehicle' => ['title' => __('Автомобили'), 'icon' => '🚗'],
                        ];

                        $initialType = $ad ? 'vehicle' : (old('section') ?? old('listing_type') ?? 'vehicle');
                        if ($initialType && !array_key_exists($initialType, $sectionCards)) {
                            $initialType = 'vehicle';
                        }

                        $initialCategory = old('category_id');
                        if ($ad) {
                            $initialCategory = $initialCategory
                                ?? ($ad['category_id'] ?? ($vehicleCategoryIds[0] ?? null));
                        } elseif (!$initialCategory && !empty($typeCategoryMap[$initialType] ?? [])) {
                            $initialCategory = $typeCategoryMap[$initialType][0];
                        }

                        if (!$initialCategory && isset($defaultVehicleCategoryId)) {
                            $initialCategory = $defaultVehicleCategoryId;
                        }

                        $vehicleOld = old('vehicle', []);

                        $oldColorKey = old('vehicle.exterior_color');
                        if ($oldColorKey && !array_key_exists($oldColorKey, $colorOptions)) {
                            $matchedColor = null;
                            foreach ($colorOptions as $key => $label) {
                                if (mb_strtolower($label) === mb_strtolower($oldColorKey)) {
                                    $matchedColor = $key;
                                    break;
                                }
                            }
                            $oldColorKey = $matchedColor ?? '';
                        }

                        $vehiclePrefill = [
                            'make' => old('vehicle.make', $adV['make'] ?? $ad['make'] ?? ''),
                            'model' => old('vehicle.model', $adV['model'] ?? $ad['model'] ?? ''),
                            'year' => old('vehicle.year', $adV['year'] ?? $ad['year'] ?? ''),
                            'mileage' => old('vehicle.mileage', $adV['mileage'] ?? $ad['mileage'] ?? ''),
                            'body_type' => old('vehicle.body_type', $adV['body_type'] ?? $ad['body_type'] ?? ''),
                            'transmission' => old('vehicle.transmission', $adV['transmission'] ?? $ad['transmission'] ?? ''),
                            'fuel_type' => old('vehicle.fuel_type', $adV['fuel_type'] ?? $ad['fuel_type'] ?? ''),
                            'engine_displacement_cc' => (string) old('vehicle.engine_displacement_cc', $adV['engine_displacement_cc'] ?? $ad['engine_displacement_cc'] ?? ''),
                            'exterior_color' => $oldColorKey !== null && $oldColorKey !== '' ? $oldColorKey : ($adV['exterior_color'] ?? ''),
                            'exterior_color_display' => $adV['exterior_color_display'] ?? $ad['exterior_color'] ?? '',
                            'brand_id' => old('vehicle.brand_id', $adV['brand_id'] ?? null),
                            'model_id' => old('vehicle.model_id', $adV['model_id'] ?? null),
                            'generation_id' => old('vehicle.generation_id', $adV['generation_id'] ?? null),
                            'buy_now_price' => old('vehicle.buy_now_price', $adV['buy_now_price'] ?? $ad['buy_now_price'] ?? ''),
                            'buy_now_currency' => old('vehicle.buy_now_currency', $adV['buy_now_currency'] ?? $ad['buy_now_currency'] ?? ''),
                            'current_bid_price' => old('vehicle.current_bid_price', $adV['current_bid_price'] ?? $ad['current_bid_price'] ?? ''),
                            'current_bid_currency' => old('vehicle.current_bid_currency', $adV['current_bid_currency'] ?? $ad['current_bid_currency'] ?? ''),
                        ];

                        $copartBuyNowPriceRaw = $vehiclePrefill['buy_now_price'];
                        $copartHasBuyNow = is_numeric($copartBuyNowPriceRaw) && (float) $copartBuyNowPriceRaw > 0;
                        $copartBuyNowPriceValue = $copartHasBuyNow ? (float) $copartBuyNowPriceRaw : null;
                        $copartBuyNowCurrency = strtoupper($vehiclePrefill['buy_now_currency'] ?: 'USD');

                        $copartCurrentBidRaw = $vehiclePrefill['current_bid_price'];
                        $copartHasCurrentBid = is_numeric($copartCurrentBidRaw) && (float) $copartCurrentBidRaw > 0;
                        $copartCurrentBidValue = $copartHasCurrentBid ? (float) $copartCurrentBidRaw : null;
                        $copartCurrentBidCurrency = strtoupper($vehiclePrefill['current_bid_currency'] ?: $copartBuyNowCurrency);

                        $defaultPrice = $ad['price'] ?? ($copartHasBuyNow ? $copartBuyNowPriceValue : null);
                        $priceInputValue = old('price', $defaultPrice);

                        $currencyOptions = [
                            'USD' => 'USD $',
                            'AMD' => '֏ AMD',
                            'RUB' => '₽ RUB',
                        ];
                        $currencyValue = strtoupper(old('currency', $ad['currency'] ?? ($copartHasBuyNow ? $copartBuyNowCurrency : 'USD')));
                        if (!array_key_exists($currencyValue, $currencyOptions)) {
                            $currencyValue = 'USD';
                        }

                        $titleValue = old('title', $ad['title'] ?? '');
                        $descriptionValue = old('description', $ad['description'] ?? '');

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
                            'colors' => $colorOptions,
                            'years' => $yearOptions,
                            'engineOptions' => $engineDisplacementOptions,
                            'initialTitle' => $titleValue,
                        ];
                    @endphp

                    @if($ad && !empty($finalPhotos))
                        <div class="mb-4">
                            <h3 class="text-lg fw-semibold mb-3">{{ __('📸 Фотографии с Copart (:count)', ['count' => count($finalPhotos)]) }}</h3>
                            <div x-data="{ mainImage: @js($mainImageDefault) }">
                                <div class="mx-auto mb-3" style="width: 220px; height: 165px; border-radius: 14px; overflow: hidden; background: #f1f3f5;">
                                    <img :src="mainImage" src="{{ $mainImageDefault }}" alt="{{ __('Главное фото') }}"
                                         style="width: 100%; height: 100%; object-fit: contain;"
                                         onerror="this.src='{{ $placeholderUrl }}'">
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($finalPhotos as $index => $photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ __('Фото :index', ['index' => $index + 1]) }}" width="70" height="70"
                                             style="border-radius: 10px; object-fit: cover; cursor: pointer; border: 2px solid #e5e7eb;"
                                             @click="mainImage = @js($photoUrl)"
                                             onerror="this.style.display='none'">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="fw-semibold mb-2">{{ __('Исправьте следующие ошибки:') }}</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('listings.store') }}"
                          enctype="multipart/form-data"
                          x-data="listingCreateForm(@js($listingFormConfig))"
                          x-init="init()"
                          x-on:submit.prevent="handleSubmit($event)"
                          class="space-y-6">
                        @csrf

                        @unless($ad)
                            <input type="hidden" name="section" :value="selectedType || ''">
                            <input type="hidden" name="listing_type" :value="listingType || ''">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('Выберите раздел') }}</label>
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

                        <input type="hidden"
                               name="category_id"
                               x-bind:value="categoryId"
                               value="{{ old('category_id', $ad['category_id'] ?? ($defaultVehicleCategoryId ?? '')) }}">

                        @if($ad)
                            <input type="hidden" name="from_auction" value="1">
                            <input type="hidden" name="listing_type" value="vehicle">
                            <input type="hidden" name="vehicle[is_from_auction]" value="1">
                            <input type="hidden" name="vehicle[source_auction_url]" value="{{ $ad['auction_url'] ?? '' }}">
                            @if(!empty($adV['auction_ends_at']))
                                <input type="hidden" name="vehicle[auction_ends_at]" value="{{ $adV['auction_ends_at'] }}">
                            @endif
                            @foreach(($finalPhotos ?? []) as $photo)
                                <input type="hidden" name="auction_photos[]" value="{{ $photo }}">
                            @endforeach
                        @endif

                        <div class="brand-surface mb-4" id="vehicle-fields" x-show="listingType === 'vehicle'" x-cloak>
                                <h3 class="h5 mb-3">{{ __('Характеристики автомобиля') }}</h3>
                                @if($ad)
                                    @php
                                        $auctionVehicleValues = [
                                            'make' => $vehiclePrefill['make'] ?? '',
                                            'model' => $vehiclePrefill['model'] ?? '',
                                            'year' => $vehiclePrefill['year'] ?? '',
                                            'mileage' => $vehiclePrefill['mileage'] ?? '',
                                            'body_type' => $vehiclePrefill['body_type'] ?? '',
                                            'transmission' => $vehiclePrefill['transmission'] ?? '',
                                            'fuel_type' => $vehiclePrefill['fuel_type'] ?? '',
                                            'engine_displacement_cc' => $vehiclePrefill['engine_displacement_cc'] ?? '',
                                            'exterior_color' => $vehiclePrefill['exterior_color'] ?? '',
                                            'exterior_color_display' => $vehiclePrefill['exterior_color_display'] ?? '',
                                            'brand_id' => $vehiclePrefill['brand_id'] ?? '',
                                            'model_id' => $vehiclePrefill['model_id'] ?? '',
                                            'generation_id' => $vehiclePrefill['generation_id'] ?? '',
                                            'buy_now_price' => $vehiclePrefill['buy_now_price'] ?? '',
                                            'buy_now_currency' => $vehiclePrefill['buy_now_currency'] ?? '',
                                            'current_bid_price' => $vehiclePrefill['current_bid_price'] ?? '',
                                            'current_bid_currency' => $vehiclePrefill['current_bid_currency'] ?? '',
                                        ];
                                        $displayExteriorColor = $auctionVehicleValues['exterior_color_display'] ?? '';
                                        if ($displayExteriorColor === '' && $auctionVehicleValues['exterior_color'] !== '') {
                                            $displayExteriorColor = $colorOptions[$auctionVehicleValues['exterior_color']] ?? $auctionVehicleValues['exterior_color'];
                                        }

                                        $auctionVehicleDisplay = [
                                            'make' => $auctionVehicleValues['make'] !== '' ? $auctionVehicleValues['make'] : '—',
                                            'model' => $auctionVehicleValues['model'] !== '' ? $auctionVehicleValues['model'] : '—',
                                            'year' => $auctionVehicleValues['year'] !== '' ? $auctionVehicleValues['year'] : '—',
                                            'mileage' => is_numeric($auctionVehicleValues['mileage'])
                                                ? number_format((int) $auctionVehicleValues['mileage'], 0, '.', ' ') . ' ' . __('км')
                                                : ($auctionVehicleValues['mileage'] !== '' ? $auctionVehicleValues['mileage'] : '—'),
                                            'body_type' => $auctionBodyTypeLabel ?? '—',
                                            'transmission' => $auctionVehicleValues['transmission'] !== ''
                                                ? ($transmissionOptions[$auctionVehicleValues['transmission']] ?? $auctionVehicleValues['transmission'])
                                                : '—',
                                            'fuel_type' => $auctionVehicleValues['fuel_type'] !== ''
                                                ? ($fuelTypeOptions[$auctionVehicleValues['fuel_type']] ?? $auctionVehicleValues['fuel_type'])
                                                : '—',
                                            'engine_displacement_cc' => is_numeric($auctionVehicleValues['engine_displacement_cc'])
                                                ? number_format((int) $auctionVehicleValues['engine_displacement_cc'], 0, '.', ' ') . ' ' . __('см³')
                                                : ($auctionVehicleValues['engine_displacement_cc'] !== '' ? $auctionVehicleValues['engine_displacement_cc'] : '—'),
                                            'exterior_color' => $displayExteriorColor !== '' ? $displayExteriorColor : '—',
                                        ];
                                    @endphp
                                    <div class="alert alert-warning mb-3 py-2 px-3">
                                        {{ __('Эти данные загружены с Copart и предназначены только для просмотра.') }}
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Марка') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['make'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Модель') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['model'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Год выпуска') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['year'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Пробег') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['mileage'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Тип кузова') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['body_type'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Трансмиссия') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['transmission'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Топливо') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['fuel_type'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Двигатель') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['engine_displacement_cc'] }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Цвет кузова') }}</label>
                                            <p class="form-control-plaintext bg-light px-3 py-2 rounded border">{{ $auctionVehicleDisplay['exterior_color'] }}</p>
                                        </div>
                                        @php
                                            $hasPreviewBuyNow = isset($auctionVehicleValues['buy_now_price']) && is_numeric($auctionVehicleValues['buy_now_price']) && (float) $auctionVehicleValues['buy_now_price'] > 0;
                                        @endphp
                                        @if($hasPreviewBuyNow)
                                            <div class="col-12">
                                                <label class="form-label">{{ __('Купить сейчас (Copart)') }}</label>
                                                <p class="form-control-plaintext bg-warning-subtle px-3 py-2 rounded border border-warning text-warning fw-semibold">
                                                    {{ number_format((float) $auctionVehicleValues['buy_now_price'], 0, '.', ' ') }}
                                                    {{ $auctionVehicleValues['buy_now_currency'] ?: 'USD' }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    @foreach(['make','model','year','mileage','body_type','transmission','fuel_type','engine_displacement_cc','exterior_color','brand_id','model_id','generation_id','buy_now_price','buy_now_currency','current_bid_price','current_bid_currency'] as $fieldName)
                                        @if(array_key_exists($fieldName, $auctionVehicleValues))
                                            <input type="hidden" name="vehicle[{{ $fieldName }}]" value="{{ $auctionVehicleValues[$fieldName] }}">
                                        @endif
                                    @endforeach
                                @else
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Марка') }}</label>
                                            <input type="hidden" name="vehicle[brand_id]" :value="vehicle.brandId">
                                            <div class="position-relative">
                                                <input type="text"
                                                       name="vehicle[make]"
                                                       class="form-control"
                                                       value="{{ old('vehicle.make', $adV['make'] ?? $ad['make'] ?? '') }}"
                                                       list="brand-options"
                                                       autocomplete="off"
                                                       x-model="vehicle.make"
                                                       @focus="ensureBrandsLoaded()"
                                                       @input="onBrandInput($event)"
                                                       @change="onBrandSelected()"
                                                       placeholder="{{ __('Начните вводить марку (например, Nissan)') }}"
                                                       x-bind:required="listingType === 'vehicle'">
                                            </div>
                                            <datalist id="brand-options">
                                                <template x-for="brand in vehicle.brands" :key="brand.id">
                                                    <option :value="brandLabel(brand)"></option>
                                                </template>
                                            </datalist>
                                            <template x-if="vehicle.loadingBrands">
                                                <small class="text-muted d-block mt-1">{{ __('Загружаем список марок…') }}</small>
                                            </template>
                                            <small class="text-muted d-block mt-1">{{ __('Выберите марку из выпадающего списка или продолжайте вводить вручную.') }}</small>
                                            <template x-if="formErrors.brand">
                                                <small class="text-danger d-block" x-text="formErrors.brand"></small>
                                            </template>
                                            @error('vehicle.brand_id')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Модель') }}</label>
                                            <input type="hidden" name="vehicle[model_id]" :value="vehicle.modelId">
                                            <div class="position-relative">
                                                <input type="text"
                                                       name="vehicle[model]"
                                                       class="form-control"
                                                       value="{{ old('vehicle.model', $adV['model'] ?? $ad['model'] ?? '') }}"
                                                       list="model-options"
                                                       autocomplete="off"
                                                       x-model="vehicle.model"
                                                       @input="onModelInput($event)"
                                                       @change="onModelSelected()"
                                                       placeholder="{{ __('Начните вводить модель (например, Rogue)') }}"
                                                       x-bind:required="listingType === 'vehicle'">
                                            </div>
                                            <datalist id="model-options">
                                                <template x-for="model in vehicle.models" :key="model.id">
                                                    <option :value="modelLabel(model)"></option>
                                                </template>
                                            </datalist>
                                            <template x-if="vehicle.loadingModels">
                                                <small class="text-muted d-block mt-1">{{ __('Загружаем модели…') }}</small>
                                            </template>
                                            <small class="text-muted d-block mt-1">
                                                {{ __('Выберите модель после выбора марки — список подстраивается автоматически.') }}
                                            </small>
                                            <template x-if="formErrors.model">
                                                <small class="text-danger d-block" x-text="formErrors.model"></small>
                                            </template>
                                            @error('vehicle.model_id')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Год выпуска') }}</label>
                                            <select name="vehicle[year]"
                                                    class="form-select"
                                                    x-model="vehicle.year"
                                                    x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Выберите год') }}</option>
                                                @foreach($yearOptions as $yearOption)
                                                    <option value="{{ $yearOption }}"
                                                        {{ (string)old('vehicle.year', $adV['year'] ?? $ad['year'] ?? '') === (string)$yearOption ? 'selected' : '' }}>
                                                        {{ $yearOption }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <template x-if="formErrors.year">
                                                <small class="text-danger d-block" x-text="formErrors.year"></small>
                                            </template>
                                            @error('vehicle.year')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Поколение') }}</label>
                                            <select class="form-select"
                                                    x-model="vehicle.generationId"
                                                    @change="handleGenerationChange"
                                                    :disabled="!vehicle.modelId || vehicle.loadingGenerations || vehicle.generations.length === 0">
                                                <option value="">{{ __('Выберите поколение') }}</option>
                                                <template x-for="generation in vehicle.generations" :key="generation.id">
                                                    <option :value="String(generation.id)" x-text="generationLabel(generation)"></option>
                                                </template>
                                            </select>
                                            <template x-if="vehicle.loadingGenerations">
                                                <small class="text-muted d-block mt-1">{{ __('Загружаем поколения…') }}</small>
                                            </template>
                                            <input type="hidden" name="vehicle[generation_id]" :value="vehicle.generationId">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Пробег (км)') }}</label>
                                            <input type="number" name="vehicle[mileage]" min="0" value="{{ old('vehicle.mileage', $adV['mileage'] ?? $ad['mileage'] ?? '') }}" class="form-control" x-bind:required="listingType === 'vehicle'">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Тип кузова') }}</label>
                                            <select name="vehicle[body_type]" class="form-select" x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Выберите') }}</option>
                                                @php
                                                    $selectedBody = old('vehicle.body_type', $adV['body_type'] ?? $ad['body_type'] ?? '');
                                                @endphp
                                                @foreach($bodyTypeOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ $selectedBody === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Трансмиссия') }}</label>
                                            <select name="vehicle[transmission]" class="form-select" x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Выберите') }}</option>
                                                @php
                                                    $selectedTransmission = old('vehicle.transmission', $adV['transmission'] ?? $ad['transmission'] ?? '');
                                                @endphp
                                                @foreach($transmissionOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ $selectedTransmission === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Топливо') }}</label>
                                            <select name="vehicle[fuel_type]" class="form-select" x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Выберите') }}</option>
                                                @php
                                                    $selectedFuelType = old('vehicle.fuel_type', $adV['fuel_type'] ?? $ad['fuel_type'] ?? '');
                                                @endphp
                                                @foreach($fuelTypeOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ $selectedFuelType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Объём двигателя') }}</label>
                                            <select name="vehicle[engine_displacement_cc]"
                                                    class="form-select"
                                                    x-model="vehicle.engine_displacement_cc"
                                                    x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Не указан') }}</option>
                                                @foreach($engineDisplacementOptions as $option)
                                                    <option value="{{ $option['cc'] }}"
                                                        {{ (string) old('vehicle.engine_displacement_cc', $vehiclePrefill['engine_displacement_cc']) === (string) $option['cc'] ? 'selected' : '' }}>
                                                        {{ $option['label'] }} ({{ $option['cc'] }} {{ __('см³') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Цвет кузова') }}</label>
                                            <select name="vehicle[exterior_color]"
                                                    class="form-select"
                                                    x-model="vehicle.exteriorColor"
                                                    x-bind:required="listingType === 'vehicle'">
                                                <option value="">{{ __('Выберите цвет') }}</option>
                                                @foreach($colorOptions as $colorKey => $colorLabel)
                                                    <option value="{{ $colorKey }}"
                                                        {{ old('vehicle.exterior_color', $vehiclePrefill['exterior_color']) === $colorKey ? 'selected' : '' }}>
                                                        {{ $colorLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <template x-if="formErrors.color">
                                                <small class="text-danger d-block" x-text="formErrors.color"></small>
                                            </template>
                                            @error('vehicle.exterior_color')
                                                <small class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Заголовок') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   value="{{ $titleValue }}"
                                   x-model="titleValue"
                                   :readonly="listingType === 'vehicle'"
                                   required>
                            @error('title')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Описание') }} <span class="text-danger">*</span></label>
                            <textarea name="description" rows="5" class="form-control" required>{{ $descriptionValue }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Цена') }} <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="price"
                                           min="0"
                                           step="1"
                                           value="{{ $priceInputValue !== null ? $priceInputValue : '' }}"
                                           class="form-control"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Валюта') }}</label>
                                    <select name="currency" class="form-select">
                                        @foreach($currencyOptions as $code => $label)
                                            <option value="{{ $code }}" @selected($currencyValue === $code)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @if($copartHasBuyNow)
                            <div class="mb-4">
                                <div class="alert alert-warning bg-amber-50 border-amber-200 text-amber-800 mb-0 rounded-3 d-flex flex-column gap-1">
                                    <span class="text-uppercase small fw-semibold">{{ __('Цена «Buy Now» на Copart') }}</span>
                                    <span class="fs-4 fw-bold">
                                        {{ number_format($copartBuyNowPriceValue, 0, '.', ' ') }} {{ $copartBuyNowCurrency }}
                                    </span>
                                    <small class="text-muted">
                                        {{ __('Мы автоматически сохраним эту цену в карточке авто (раздел «Купить сейчас»).') }}
                                    </small>
                                </div>
                            </div>
                        @endif
                        @if($copartHasCurrentBid)
                            <div class="mb-4">
                                <div class="alert alert-info bg-indigo-50 border border-indigo-200 text-indigo-900 mb-0 rounded-3 d-flex flex-column gap-1">
                                    <span class="text-uppercase small fw-semibold">{{ __('Текущая ставка на Copart') }}</span>
                                    <span class="fs-5 fw-bold">
                                        {{ number_format($copartCurrentBidValue, 0, '.', ' ') }} {{ $copartCurrentBidCurrency }}
                                    </span>
                                    <small class="text-muted">
                                        {{ __('Показатель меняется на Copart; мы обновляем его ежедневно автоматически.') }}
                                    </small>
                                </div>
                            </div>
                        @endif

                    @if(! $ad)
                        @include('listings.partials.region-dropdown', [
                            'regions' => $regions,
                            'selectedRegion' => old('region_id'),
                            'fieldId' => 'region_id',
                            'fieldName' => 'region_id',
                            'label' => __('Регион'),
                            'required' => true,
                        ])

                        <div>
                            <label class="form-label">{{ __('Изображения') }}</label>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control">
                            <small class="text-muted">{{ __('PNG, JPG, WEBP до 5MB') }}</small>
                        </div>
                    @endif

                        <div class="d-flex justify-content-end gap-3 pt-3">
                            <a href="{{ route('home') }}" class="btn-brand-outline">{{ __('Отмена') }}</a>
                            <button type="submit" class="btn-brand-gradient" :disabled="!formVisible">
                                {{ $ad ? __('🚀 Создать объявление с аукциона') : __('Создать объявление') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@push('styles')
    <style>
        .auction-countdown-banner .countdown-units > div {
            min-width: 96px;
        }

        .auction-countdown-banner[data-countdown-state="expired"] .countdown-units {
            opacity: 0.4;
        }
    </style>
@endpush

@include('listings.partials.vehicle-form-script')
@endsection

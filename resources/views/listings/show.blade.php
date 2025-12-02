<x-app-layout>
    <div class="py-12 listing-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @php
                        $fallbackImage = asset('images/no-image.jpg');
                        $galleryImages = [];
                        $mediaUrlResolver = function ($media) {
                            if (!is_object($media) || !method_exists($media, 'getKey')) {
                                return null;
                            }

                            $useConversion = method_exists($media, 'hasGeneratedConversion') && $media->hasGeneratedConversion('medium');

                            try {
                                $params = ['media' => $media->getKey()];
                                if ($useConversion) {
                                    $params['conversion'] = 'medium';
                                }

                                return route('media.show', $params);
                            } catch (\Throwable $e) {
                                try {
                                    return $useConversion ? $media->getUrl('medium') : $media->getUrl();
                                } catch (\Throwable $_) {
                                    return null;
                                }
                            }
                        };

                        if (method_exists($listing, 'hasMedia') && $listing->hasMedia('images')) {
                            foreach ($listing->getMedia('images') as $m) {
                                $url = $mediaUrlResolver($m);
                                if ($url) {
                                    $galleryImages[] = $url;
                                }
                            }
                        }

                        if (method_exists($listing, 'hasMedia') && $listing->hasMedia('auction_photos')) {
                            foreach ($listing->getMedia('auction_photos') as $m) {
                                $url = $mediaUrlResolver($m);
                                if ($url) {
                                    $galleryImages[] = $url;
                                }
                            }
                        }

                        if (!empty($listing->vehicleDetail)) {
                            if (!empty($listing->vehicleDetail->preview_image_url)) {
                                $galleryImages[] = $listing->vehicleDetail->preview_image_url;
                            }
                            if (!empty($listing->vehicleDetail->main_image_url)) {
                                $galleryImages[] = $listing->vehicleDetail->main_image_url;
                            }
                        }

                        if (!empty($listing->auction_photo_urls)) {
                            $rawPhotos = is_array($listing->auction_photo_urls)
                                ? $listing->auction_photo_urls
                                : json_decode($listing->auction_photo_urls, true);
                            if (is_array($rawPhotos)) {
                                foreach ($rawPhotos as $photo) {
                                    if (!empty($photo)) {
                                        $galleryImages[] = $photo;
                                    }
                                }
                            }
                        }

                        if (!empty($listing->media) && is_iterable($listing->media)) {
                            foreach ($listing->media as $m) {
                                $url = $mediaUrlResolver($m);
                                if ($url) {
                                    $galleryImages[] = $url;
                                }
                            }
                        }

                        $galleryImages = array_values(array_filter(array_unique($galleryImages)));
                        if (empty($galleryImages)) {
                            $galleryImages[] = $fallbackImage;
                        }

                        $seller = $listing->user;
                        $sellerPhone = $seller?->phone;
                        $sellerPhoneVerified = $seller?->phone_verified_at;
                        $sellerAvatar = $seller?->avatar
                            ? (\Illuminate\Support\Str::startsWith($seller->avatar, ['http://', 'https://'])
                                ? $seller->avatar
                                : \Illuminate\Support\Facades\Storage::url($seller->avatar))
                            : 'https://ui-avatars.com/api/?name=' . urlencode($seller->name ?? 'Seller') . '&background=111827&color=ffffff';
                        $sellerJoined = $seller?->created_at?->format('d.m.Y');
                        $telHref = $sellerPhone ? preg_replace('/[^0-9+]/', '', $sellerPhone) : null;
                    @endphp

                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.85fr)_340px]">
                        <div class="space-y-8">
                            <header>
                                <h1 class="text-3xl font-bold">{{ $listing->title }}</h1>
                                <div class="mt-4 flex items-center flex-wrap gap-2">
                                    @auth
                                        <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST" data-favorite-toggle>
                                            @csrf
                                            <button type="submit" class="p-2 rounded-full border hover:bg-gray-100" aria-label="{{ __('Добавить/убрать из избранного') }}">
                                                @if(auth()->user()->favorites->contains($listing))
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endauth
                                    @can('update', $listing)
                                        <a href="{{ route('listings.edit', $listing) }}" class="btn btn-sm btn-outline-secondary">{{ __('Редактировать') }}</a>
                                    @endcan
                                    @can('delete', $listing)
                                        <form action="{{ route('listings.destroy', $listing) }}" method="POST" onsubmit="return confirm('{{ __('Вы уверены?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('Удалить') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </header>

                            @php
                                $sliderConfig = [
                                    'images' => $galleryImages,
                                    'fallback' => $fallbackImage,
                                ];
                            @endphp
                            <div
                                class="listing-slider"
                                x-data='listingSlider(@json($sliderConfig))'
                                @keydown.window.arrow-left.prevent="prev()"
                                @keydown.window.arrow-right.prevent="next()"
                            >
                                <div class="listing-slider__viewport">
                                    <img :src="currentImage" alt="{{ $listing->title }}" class="listing-slider__image" @@error="handleError($event)">
                                    <button type="button" class="listing-slider__nav listing-slider__nav--prev" @click="prev()" x-show="images.length > 1" x-cloak aria-label="{{ __('Предыдущее фото') }}">
                                        <span aria-hidden="true">&larr;</span>
                                    </button>
                                    <button type="button" class="listing-slider__nav listing-slider__nav--next" @click="next()" x-show="images.length > 1" x-cloak aria-label="{{ __('Следующее фото') }}">
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </div>
                                <div class="listing-thumbs" x-show="images.length > 1" x-cloak>
                                    <template x-for="(thumb, idx) in images" :key="idx">
                                        <button type="button"
                                                class="listing-thumb"
                                                :class="{ 'is-active': idx === index }"
                                                @click="go(idx)"
                                                aria-label="{{ __('Миниатюра фото') }}">
                                            <img :src="thumb" alt="{{ $listing->title }}" @@error="handleError($event)">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="text-gray-600 flex flex-wrap gap-2">
                                <span>{{ __('Опубликовано: :date', ['date' => $listing->created_at->format('d.m.Y')]) }}</span>
                                <span>&middot;</span>
                                <span>{{ __('Регион: :region', ['region' => $listing->region?->localized_name ?? __('Без региона')]) }}</span>
                            </div>

                            @php
                                $vehicleDetail = $listing->vehicleDetail;
                                $buyNowPrice = $vehicleDetail?->buy_now_price;
                                $buyNowCurrency = $vehicleDetail?->buy_now_currency ?: $listing->currency;
                                $operationalStatus = $vehicleDetail?->operational_status;
                                $isBuyNowAvailable = $buyNowPrice !== null;
                                $displayPrice = $isBuyNowAvailable ? $buyNowPrice : $listing->price;
                                $displayCurrency = $isBuyNowAvailable ? $buyNowCurrency : $listing->currency;
                                $currentBidPrice = $vehicleDetail?->current_bid_price;
                                $currentBidCurrency = $vehicleDetail?->current_bid_currency ?: $displayCurrency;
                                $currentBidFetchedAt = $vehicleDetail?->current_bid_fetched_at;
                                $outTheDoor = $listing->out_the_door_price;
                                $priceBadge = $listing->price_badge;
                                $priceBadgeLabels = [
                                    'great' => __('Great Deal'),
                                    'fair' => __('Fair Deal'),
                                    'overpriced' => __('Overpriced'),
                                    'unknown' => __('Цена не оценена'),
                                ];
                                $priceBadgeLabel = $priceBadge ? ($priceBadgeLabels[$priceBadge] ?? $priceBadge) : null;
                            @endphp

                            <div class="flex flex-col gap-1">
                                @if($isBuyNowAvailable)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Купить сейчас') }}</span>
                                @endif
                                <span class="text-4xl font-extrabold text-indigo-600 leading-tight">
                                    @if($displayPrice !== null)
                                        {{ number_format($displayPrice, 0, '.', ' ') }} {{ $displayCurrency }}
                                    @else
                                        {{ __('Цена уточняется') }}
                                    @endif
                                </span>
                            </div>
                            @if($outTheDoor)
                                <div class="mt-2 text-base text-slate-700">
                                    {{ __('Итоговая стоимость (OTD):') }} <strong>{{ number_format($outTheDoor, 0, '.', ' ') }} {{ $displayCurrency }}</strong>
                                </div>
                            @endif
                            @if($priceBadgeLabel)
                                <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                    {{ $priceBadgeLabel }}
                                    @if($listing->price_anomaly)
                                        <span class="bg-amber-400 text-slate-900 rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">{{ __('Проверить цену') }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($isBuyNowAvailable)
                                <div class="mt-3 inline-flex flex-wrap items-baseline gap-3 rounded-xl bg-amber-50 px-4 py-3 text-amber-800">
                                    <span class="text-xs font-semibold uppercase tracking-wide">{{ __('Купить сейчас') }}</span>
                                    <span class="text-2xl font-bold">
                                        {{ number_format($buyNowPrice, 0, '.', ' ') }} {{ $buyNowCurrency }}
                                    </span>
                                </div>
                            @endif
                            @if($currentBidPrice !== null)
                                <div class="mt-3 inline-flex flex-wrap items-baseline gap-3 rounded-xl bg-indigo-50 px-4 py-3 text-indigo-900">
                                    <span class="text-xs font-semibold uppercase tracking-wide">{{ __('Текущая ставка') }}</span>
                                    <span class="text-2xl font-bold">
                                        {{ number_format($currentBidPrice, 0, '.', ' ') }} {{ $currentBidCurrency }}
                                    </span>
                                    @if($currentBidFetchedAt)
                                        <span class="text-xs text-indigo-600">
                                            {{ __('обновлено :date', ['date' => $currentBidFetchedAt->timezone(config('app.timezone'))->format('d.m.Y H:i')]) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            @if($operationalStatus)
                                <div class="mt-2 text-base font-semibold text-emerald-700">
                                    {{ __('Состояние:') }} {{ $operationalStatus }}
                                </div>
                            @endif

                            <div class="prose max-w-none">
                                <h2 class="text-xl font-bold">{{ __('Описание') }}</h2>
                                @if($listing->description)
                                    <p>{{ $listing->description }}</p>
                                @endif
                            </div>

                            @if($listing->listing_type === 'vehicle' && $listing->vehicleDetail)
                                @php
                                    $vehicle = $listing->vehicleDetail;
                                    $bodyTypeMap = [
                                        'sedan' => __('Седан'),
                                        'suv' => __('SUV / Внедорожник'),
                                        'coupe' => __('Купе'),
                                        'hatchback' => __('Хэтчбек'),
                                        'wagon' => __('Универсал'),
                                        'pickup' => __('Пикап'),
                                        'minivan' => __('Минивэн'),
                                        'convertible' => __('Кабриолет'),
                                    ];
                                    $transmissionMap = [
                                        'automatic' => __('Автоматическая'),
                                        'manual' => __('Механическая'),
                                        'cvt' => __('Вариатор (CVT)'),
                                        'semi-automatic' => __('Полуавтоматическая'),
                                    ];
                                    $fuelTypeMap = [
                                        'gasoline' => __('Бензин'),
                                        'diesel' => __('Дизель'),
                                        'hybrid' => __('Гибрид'),
                                        'electric' => __('Электро'),
                                        'lpg' => __('ГБО'),
                                    ];
                                    $mileageText = $vehicle->mileage !== null
                                        ? number_format($vehicle->mileage, 0, '.', ' ') . ' ' . __('км')
                                        : '—';
                                    $auctionEndsAt = $vehicle->auction_ends_at
                                        ? $vehicle->auction_ends_at->timezone(config('app.timezone'))
                                        : null;
                                    $auctionEndsAtText = $auctionEndsAt?->format('d.m.Y H:i');
                                    $auctionEndsAtIso = $auctionEndsAt?->toIso8601String();
                                @endphp
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                    <h2 class="text-2xl font-bold mb-4 text-blue-900">{{ __('Характеристики автомобиля') }}</h2>
                                    @if($auctionEndsAtIso)
                                        <div class="bg-white border border-dashed border-blue-200 rounded-lg p-4 mb-5"
                                             data-countdown
                                             data-expires="{{ $auctionEndsAtIso }}"
                                             data-prefix="{{ __('До конца') }}"
                                             data-expired-text="{{ __('Аукцион завершён') }}"
                                             data-day-label="{{ __('д') }}">
                                            <div class="text-xs text-blue-800 fw-semibold text-uppercase mb-1">
                                                {{ __('Аукцион завершается') }}
                                            </div>
                                            <div class="text-2xl fw-bold text-blue-900" data-countdown-text>{{ __('Загрузка…') }}</div>
                                            @if($auctionEndsAtText)
                                                <div class="text-sm text-muted mt-1">
                                                    {{ __('Ожидаемая дата: :date (:tz)', ['date' => $auctionEndsAtText, 'tz' => config('app.timezone')]) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($vehicle->make)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Марка:') }}</span>
                                                <strong class="text-gray-900">{{ $vehicle->make }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->model)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Модель:') }}</span>
                                                <strong class="text-gray-900">{{ $vehicle->model }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->year)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Год выпуска:') }}</span>
                                                <strong class="text-gray-900">{{ $vehicle->year }}</strong>
                                            </div>
                                        @endif
                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-gray-600 font-medium">{{ __('Пробег:') }}</span>
                                            <strong class="text-gray-900">{{ $mileageText }}</strong>
                                        </div>
                                        @if($vehicle->transmission)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Коробка передач:') }}</span>
                                                <strong class="text-gray-900">{{ $transmissionMap[$vehicle->transmission] ?? $vehicle->transmission }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->fuel_type)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Тип топлива:') }}</span>
                                                <strong class="text-gray-900">{{ $fuelTypeMap[$vehicle->fuel_type] ?? $vehicle->fuel_type }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->body_type)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Тип кузова:') }}</span>
                                                <strong class="text-gray-900">{{ $bodyTypeMap[$vehicle->body_type] ?? $vehicle->body_type }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->engine_displacement_cc)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Объём двигателя:') }}</span>
                                                <strong class="text-gray-900">{{ number_format($vehicle->engine_displacement_cc, 0, '.', ' ') }} {{ __('см³') }}</strong>
                                            </div>
                                        @endif
                                        @if($vehicle->exterior_color)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Цвет:') }}</span>
                                                <strong class="text-gray-900">{{ $vehicle->exterior_color }}</strong>
                                            </div>
                                        @endif
                                        @if($auctionEndsAtText)
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium">{{ __('Окончание аукциона:') }}</span>
                                                <strong class="text-gray-900">{{ $auctionEndsAtText }}</strong>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            @endif
                        </div>

                        <aside class="seller-card lg:row-span-2">
                            <div class="seller-card__header">
                                <img src="{{ $sellerAvatar }}" alt="{{ $seller->name ?? __('Продавец') }}" class="seller-card__avatar">
                                <div>
                                    <p class="seller-card__title">{{ $seller->name ?? __('Продавец') }}</p>
                                    @if($sellerJoined)
                                        <p class="seller-card__muted">{{ __('На сервисе с :date', ['date' => $sellerJoined]) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="seller-card__section">
                                <p class="seller-card__label">{{ __('Номер телефона') }}</p>
                                @if($sellerPhone && $sellerPhoneVerified)
                                    <div class="seller-card__phone">
                                        <a href="tel:{{ $telHref }}" id="sellerPhoneValue">{{ $sellerPhone }}</a>
                                        <button type="button" class="seller-card__copy" data-copy-target="#sellerPhoneValue">
                                            <i class="fa-solid fa-copy"></i> {{ __('Скопировать') }}
                                        </button>
                                    </div>
                                    <p class="seller-card__muted">{{ __('Номер подтверждён через SMS') }}</p>
                                @else
                                    <p class="seller-card__muted">{{ __('Телефон не указан') }}</p>
                                @endif
                            </div>

                            <div class="seller-card__actions">
                                <a href="#contactSeller" class="btn btn-brand-gradient w-100">
                                    <i class="fa-solid fa-message me-2"></i>
                                    {{ __('Написать продавцу') }}
                                </a>
                            </div>
                        </aside>

                        <div class="space-y-8">
                            @if($listing->customFieldValues->isNotEmpty())
                                <div>
                                    <h4 class="text-xl font-bold mb-4">{{ __('Характеристики') }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                                        @foreach($listing->customFieldValues as $customValue)
                                            <div>
                                                <span class="text-gray-600">{{ $customValue->field->name ?? __('Неизвестное поле') }}:</span>
                                                <strong class="text-gray-900 ml-2">{{ $customValue->value }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($relatedListings->isNotEmpty())
                                <div class="border-t pt-6">
                                    <h3 class="text-2xl font-bold mb-6">{{ __('Похожие объявления') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($relatedListings as $item)
                                <x-listing.card :listing="$item" :showFavorite="false" />
                            @endforeach
                        </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @once
        @push('styles')
            <style>
                .listing-slider__viewport {
                    position: relative;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 1.25rem;
                    padding: 0.5rem 1rem;
                    min-height: 280px;
                    max-height: 520px;
                    aspect-ratio: 4 / 3;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }
                .listing-slider__image {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    background: #ffffff;
                    border-radius: 0.6rem;
                    box-shadow: inset 0 0 30px rgba(15, 23, 42, 0.04);
                }
                .listing-slider__nav {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 46px;
                    height: 46px;
                    border-radius: 999px;
                    border: none;
                    background: rgba(15, 23, 42, 0.85);
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: background 0.2s, transform 0.2s;
                }
                .listing-slider__nav:hover {
                    background: rgba(79, 70, 229, 0.95);
                }
                .listing-slider__nav--prev { left: 1rem; }
                .listing-slider__nav--next { right: 1rem; }
                @media (max-width: 640px) {
                    .listing-slider__viewport {
                        padding: 0.5rem;
                        min-height: 240px;
                        width: 100vw;
                        margin-left: calc(50% - 50vw);
                        margin-right: calc(50% - 50vw);
                        border-radius: 0;
                    }
                    .listing-slider__image {
                    border-radius: 0;
                }
                .listing-slider__nav { width: 38px; height: 38px; }
            }
            .listing-slider {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 110px;
                gap: 12px;
                align-items: start;
            }
            .listing-thumbs {
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-height: 520px;
                overflow-y: auto;
                padding: 4px 2px;
            }
            .listing-thumb {
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                overflow: hidden;
                width: 100%;
                aspect-ratio: 4 / 3;
                background: #fff;
                padding: 2px;
                transition: all 0.18s ease;
            }
            .listing-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 8px;
            }
            .listing-thumb.is-active {
                border-color: #e32b2b;
                box-shadow: 0 8px 20px rgba(227, 43, 43, 0.18);
            }
            @media (max-width: 640px) {
                .listing-slider {
                    grid-template-columns: 1fr;
                }
                .listing-thumbs {
                    flex-direction: row;
                    max-height: none;
                    overflow-y: visible;
                    overflow-x: auto;
                }
                .listing-thumb {
                    width: 82px;
                    height: 62px;
                }
                .listing-page h1,
                .listing-page .text-3xl {
                    font-size: 1.5rem !important;
                }
                    .listing-page .text-4xl {
                        font-size: 1.75rem !important;
                    }
                    .listing-page .text-2xl {
                        font-size: 1.25rem !important;
                    }
                    .listing-page .text-xl {
                        font-size: 1.125rem !important;
                    }
                    .listing-page .text-lg {
                        font-size: 1rem !important;
                    }
                    .listing-page .text-base {
                        font-size: 0.95rem !important;
                    }
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('listingSlider', (config = {}) => {
                        const images = Array.isArray(config.images)
                            ? config.images.filter(i => typeof i === 'string' && i.trim() !== '')
                            : [];
                        const fallback = (typeof config.fallback === 'string' && config.fallback.trim() !== '') ? config.fallback : '';
                        if (!images.length && fallback) images.push(fallback);

                        return {
                            images,
                            fallback,
                            index: 0,
                            get currentImage() {
                                return this.images[this.index] ?? this.fallback;
                            },
                            prev() {
                                if (this.images.length > 1) {
                                    this.index = (this.index - 1 + this.images.length) % this.images.length;
                                }
                            },
                            next() {
                                if (this.images.length > 1) {
                                    this.index = (this.index + 1) % this.images.length;
                                }
                            },
                            go(idx) {
                                if (this.images.length) {
                                    this.index = Math.max(0, Math.min(idx, this.images.length - 1));
                                }
                            },
                            handleError(event) {
                                if (event?.target && this.fallback && event.target.src !== this.fallback) {
                                    event.target.src = this.fallback;
                                }
                            },
                        };
                    });
                });

                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('[data-copy-target]').forEach((button) => {
                        button.addEventListener('click', async () => {
                            const target = document.querySelector(button.dataset.copyTarget);
                            if (!target) return;
                            try {
                                await navigator.clipboard.writeText(target.textContent.trim());
                                button.classList.add('is-copied');
                                setTimeout(() => button.classList.remove('is-copied'), 1500);
                            } catch (error) {
                                console.error('Copy failed', error);
                            }
                        });
                    });
                });
            </script>
        @endpush
    @endonce
</x-app-layout>

@push('head')
    @include('components.seo.vehicle-jsonld', ['listing' => $listing])
@endpush

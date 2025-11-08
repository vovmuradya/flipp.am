<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @php
                        $fallbackImage = asset('images/no-image.jpg');
                        $galleryImages = [];

                        if (method_exists($listing, 'hasMedia') && $listing->hasMedia('images')) {
                            foreach ($listing->getMedia('images') as $m) {
                                try {
                                    $url = $m->getUrl('medium') ?: $m->getUrl();
                                } catch (\Throwable $e) {
                                    $url = null;
                                }
                                if ($url) {
                                    $galleryImages[] = $url;
                                }
                            }
                        }

                        if (method_exists($listing, 'hasMedia') && $listing->hasMedia('auction_photos')) {
                            foreach ($listing->getMedia('auction_photos') as $m) {
                                try {
                                    $url = $m->getUrl('medium') ?: $m->getUrl();
                                } catch (\Throwable $e) {
                                    $url = null;
                                }
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

                        if (!empty($listing->auction_photos)) {
                            $rawPhotos = is_array($listing->auction_photos)
                                ? $listing->auction_photos
                                : json_decode($listing->auction_photos, true);
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
                                try {
                                    $url = method_exists($m, 'getUrl') ? $m->getUrl() : null;
                                } catch (\Throwable $e) {
                                    $url = null;
                                }
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

                    <div class="lg:grid lg:grid-cols-[minmax(0,1.85fr)_340px] lg:gap-8">
                        <div class="space-y-8">
                            <header>
                                <h1 class="text-3xl font-bold">{{ $listing->title }}</h1>
                                <div class="mt-4 flex items-center flex-wrap gap-2">
                                    @auth
                                        <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST">
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

                                <template x-if="images.length > 1">
                                    <div class="listing-slider__thumbs mt-3" x-cloak>
                                        <template x-for="(img, idx) in images" :key="idx">
                                            <button type="button" class="listing-slider__thumb" :class="{ 'is-active': idx === index }" @click="go(idx)">
                                                <img :src="img" :alt="'{{ addslashes($listing->title) }} - ' + @js(__('фото')) + ' ' + (idx + 1)" @@error="handleError($event)">
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <div class="text-gray-600 flex flex-wrap gap-2">
                                <span>{{ __('Опубликовано: :date', ['date' => $listing->created_at->format('d.m.Y')]) }}</span>
                                <span>&middot;</span>
                                <span>{{ __('Регион: :region', ['region' => $listing->region->name ?? __('Без региона')]) }}</span>
                            </div>

                            <div class="text-4xl font-extrabold text-indigo-600">
                                ${{ number_format($listing->price, 0, '.', ' ') }}
                            </div>

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
                                    $auctionEndsAtText = $vehicle->auction_ends_at
                                        ? $vehicle->auction_ends_at->timezone(config('app.timezone'))->format('d.m.Y H:i')
                                        : null;
                                @endphp
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                    <h2 class="text-2xl font-bold mb-4 text-blue-900">{{ __('Характеристики автомобиля') }}</h2>
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

                                    @if($vehicle->is_from_auction && $vehicle->source_auction_url)
                                        <div class="mt-6 pt-4 border-t border-blue-300">
                                            <a href="{{ $vehicle->source_auction_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>
                                                {{ __('Посмотреть оригинальное объявление на аукционе') }}
                                            </a>
                                            <p class="mt-2 text-sm text-blue-700">
                                                {{ __('Это объявление было создано на основе лота с аукциона') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

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

                            @auth
                                <div class="border-t pt-6" id="contactSeller">
                                    <h3 class="text-xl font-bold mb-4">{{ __('Связаться с продавцом') }}</h3>
                                    @if(auth()->id() === $listing->user_id)
                                        <p class="text-gray-500">{{ __('Это ваше объявление.') }}</p>
                                    @else
                                        @if(session('success_message'))
                                            <div class="mb-4 text-green-600 font-semibold">{{ session('success_message') }}</div>
                                        @endif
                                        <form action="{{ route('listings.messages.store', $listing) }}" method="POST">
                                            @csrf
                                            <div>
                                                <textarea name="body" rows="4" class="w-full border-gray-300 rounded-md" placeholder="{{ __('Напишите ваше сообщение...') }}" required minlength="10"></textarea>
                                                @error('body')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                            </div>
                                            <div class="mt-4">
                                                <x-primary-button>{{ __('Отправить сообщение') }}</x-primary-button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <div class="border-t pt-6" id="contactSeller">
                                    <h3 class="text-xl font-bold mb-4">{{ __('Связаться с продавцом') }}</h3>
                                    <p class="text-gray-500 mb-4">{{ __('Войдите в аккаунт, чтобы написать продавцу.') }}</p>
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">{{ __('Войти') }}</a>
                                </div>
                            @endauth

                            <div class="border-t pt-6">
                                <h3 class="text-xl font-bold mb-4">{{ __('Отзывы о продавце') }}</h3>
                                <div class="space-y-4">
                                    @forelse($listing->reviews as $review)
                                        <div class="border-b pb-2">
                                            <div class="flex items-center mb-1">
                                                <span class="font-semibold">{{ $review->reviewer->name ?? __('Анонимный пользователь') }}</span>
                                                <div class="ml-2 flex text-yellow-400">
                                                    @for ($i = 0; $i < $review->rating; $i++)
                                                        ★
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="text-gray-700">{{ $review->comment }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('d.m.Y') }}</p>
                                        </div>
                                    @empty
                                        <p class="text-gray-500">{{ __('Отзывов пока нет.') }}</p>
                                    @endforelse
                                </div>

                                @auth
                                    @if(auth()->id() !== $listing->user_id && !$listing->reviews->contains('reviewer_id', auth()->id()))
                                        <div class="mt-6">
                                            <h4 class="text-lg font-semibold mb-2">{{ __('Оставить отзыв') }}</h4>
                                            <form action="{{ route('listings.reviews.store', $listing) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="rating" value="0">
                                                <div class="flex items-center space-x-1 text-gray-400 flex-row-reverse justify-end">
                                                    @for($i=5; $i>=1; $i--)
                                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" id="rate-{{ $i }}">
                                                        <label for="rate-{{ $i }}" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                                    @endfor
                                                    <label class="mr-2">{{ __('Оценка:') }}</label>
                                                </div>
                                                @error('rating')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror

                                                <div class="mt-4">
                                                    <textarea name="comment" rows="4" class="w-full border-gray-300 rounded-md" placeholder="{{ __('Напишите ваш отзыв...') }}" required minlength="10"></textarea>
                                                    @error('comment')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                                </div>

                                                <div class="mt-4">
                                                    <x-primary-button>{{ __('Отправить отзыв') }}</x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </div>

                            @if($relatedListings->isNotEmpty())
                                <div class="border-t pt-6">
                                    <h3 class="text-2xl font-bold mb-6">{{ __('Похожие объявления') }}</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        @foreach($relatedListings as $item)
                                            @php
                                                $relatedImage = $item->getFirstMediaUrl('images', 'medium')
                                                    ?: $item->getFirstMediaUrl('images')
                                                    ?: $item->getFirstMediaUrl('auction_photos', 'medium')
                                                    ?: $item->getFirstMediaUrl('auction_photos')
                                                    ?: $item->vehicleDetail->preview_image_url
                                                    ?? $item->vehicleDetail->main_image_url
                                                    ?? asset('images/no-image.jpg');
                                            @endphp
                                            <a href="{{ route('listings.show', $item) }}" class="block bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                                                <div class="bg-gray-100" style="aspect-ratio: 4 / 3;">
                                                    <img src="{{ $relatedImage }}" alt="{{ $item->title }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/no-image.jpg') }}'">
                                                </div>
                                                <div class="p-3">
                                                    <h4 class="font-semibold text-gray-900 line-clamp-1">{{ $item->title }}</h4>
                                                    <p class="text-xs text-gray-500 mb-2">{{ $item->region->name ?? __('Без региона') }}</p>
                                                    <p class="text-indigo-600 font-bold text-sm">{{ number_format($item->price, 0, '.', ' ') }} {{ $item->currency }}</p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <aside class="seller-card">
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
                    padding: 1.25rem;
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
                    border-radius: 0.85rem;
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
                .listing-slider__thumbs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.75rem;
                }
                .listing-slider__thumb {
                    width: 110px;
                    height: 78px;
                    border-radius: 0.75rem;
                    border: 2px solid transparent;
                    overflow: hidden;
                    background: #f1f5f9;
                    padding: 0;
                    cursor: pointer;
                    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
                }
                .listing-slider__thumb img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    pointer-events: none;
                }
                .listing-slider__thumb:hover { transform: translateY(-2px); }
                .listing-slider__thumb.is-active {
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
                }
                @media (max-width: 640px) {
                    .listing-slider__viewport { padding: 0.75rem; min-height: 220px; }
                    .listing-slider__nav { width: 38px; height: 38px; }
                    .listing-slider__thumb { width: 86px; height: 62px; }
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

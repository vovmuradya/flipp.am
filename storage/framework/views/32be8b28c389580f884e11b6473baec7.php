<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <?php
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
                    ?>

                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.85fr)_340px]">
                        <div class="space-y-8">
                            <header>
                                <h1 class="text-3xl font-bold"><?php echo e($listing->title); ?></h1>
                                <div class="mt-4 flex items-center flex-wrap gap-2">
                                    <?php if(auth()->guard()->check()): ?>
                                        <form action="<?php echo e(route('listings.favorite.toggle', $listing)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="p-2 rounded-full border hover:bg-gray-100" aria-label="<?php echo e(__('Добавить/убрать из избранного')); ?>">
                                                <?php if(auth()->user()->favorites->contains($listing)): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $listing)): ?>
                                        <a href="<?php echo e(route('listings.edit', $listing)); ?>" class="btn btn-sm btn-outline-secondary"><?php echo e(__('Редактировать')); ?></a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $listing)): ?>
                                        <form action="<?php echo e(route('listings.destroy', $listing)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('Вы уверены?')); ?>');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger"><?php echo e(__('Удалить')); ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <?php
                                $sliderConfig = [
                                    'images' => $galleryImages,
                                    'fallback' => $fallbackImage,
                                ];
                            ?>
                            <div
                                class="listing-slider"
                                x-data='listingSlider(<?php echo json_encode($sliderConfig, 15, 512) ?>)'
                                @keydown.window.arrow-left.prevent="prev()"
                                @keydown.window.arrow-right.prevent="next()"
                            >
                                <div class="listing-slider__viewport">
                                    <img :src="currentImage" alt="<?php echo e($listing->title); ?>" class="listing-slider__image" @error="handleError($event)">
                                    <button type="button" class="listing-slider__nav listing-slider__nav--prev" @click="prev()" x-show="images.length > 1" x-cloak aria-label="<?php echo e(__('Предыдущее фото')); ?>">
                                        <span aria-hidden="true">&larr;</span>
                                    </button>
                                    <button type="button" class="listing-slider__nav listing-slider__nav--next" @click="next()" x-show="images.length > 1" x-cloak aria-label="<?php echo e(__('Следующее фото')); ?>">
                                        <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </div>

                                <template x-if="images.length > 1">
                                    <div class="listing-slider__thumbs mt-3" x-cloak>
                                        <template x-for="(img, idx) in images" :key="idx">
                                            <button type="button" class="listing-slider__thumb" :class="{ 'is-active': idx === index }" @click="go(idx)">
                                                <img :src="img" :alt="'<?php echo e(addslashes($listing->title)); ?> - ' + <?php echo \Illuminate\Support\Js::from(__('фото'))->toHtml() ?> + ' ' + (idx + 1)" @error="handleError($event)">
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <div class="text-gray-600 flex flex-wrap gap-2">
                                <span><?php echo e(__('Опубликовано: :date', ['date' => $listing->created_at->format('d.m.Y')])); ?></span>
                                <span>&middot;</span>
                                <span><?php echo e(__('Регион: :region', ['region' => $listing->region?->localized_name ?? __('Без региона')])); ?></span>
                            </div>

                            <?php
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
                            ?>

                            <div class="flex flex-col gap-1">
                                <?php if($isBuyNowAvailable): ?>
                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Купить сейчас')); ?></span>
                                <?php endif; ?>
                                <span class="text-4xl font-extrabold text-indigo-600 leading-tight">
                                    <?php if($displayPrice !== null): ?>
                                        <?php echo e(number_format($displayPrice, 0, '.', ' ')); ?> <?php echo e($displayCurrency); ?>

                                    <?php else: ?>
                                        <?php echo e(__('Цена уточняется')); ?>

                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if($isBuyNowAvailable): ?>
                                <div class="mt-3 inline-flex flex-wrap items-baseline gap-3 rounded-xl bg-amber-50 px-4 py-3 text-amber-800">
                                    <span class="text-xs font-semibold uppercase tracking-wide"><?php echo e(__('Купить сейчас')); ?></span>
                                    <span class="text-2xl font-bold">
                                        <?php echo e(number_format($buyNowPrice, 0, '.', ' ')); ?> <?php echo e($buyNowCurrency); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if($currentBidPrice !== null): ?>
                                <div class="mt-3 inline-flex flex-wrap items-baseline gap-3 rounded-xl bg-indigo-50 px-4 py-3 text-indigo-900">
                                    <span class="text-xs font-semibold uppercase tracking-wide"><?php echo e(__('Текущая ставка')); ?></span>
                                    <span class="text-2xl font-bold">
                                        <?php echo e(number_format($currentBidPrice, 0, '.', ' ')); ?> <?php echo e($currentBidCurrency); ?>

                                    </span>
                                    <?php if($currentBidFetchedAt): ?>
                                        <span class="text-xs text-indigo-600">
                                            <?php echo e(__('обновлено :date', ['date' => $currentBidFetchedAt->timezone(config('app.timezone'))->format('d.m.Y H:i')])); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if($operationalStatus): ?>
                                <div class="mt-2 text-base font-semibold text-emerald-700">
                                    <?php echo e(__('Состояние:')); ?> <?php echo e($operationalStatus); ?>

                                </div>
                            <?php endif; ?>

                            <div class="prose max-w-none">
                                <h2 class="text-xl font-bold"><?php echo e(__('Описание')); ?></h2>
                                <?php if($listing->description): ?>
                                    <p><?php echo e($listing->description); ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if($listing->listing_type === 'vehicle' && $listing->vehicleDetail): ?>
                                <?php
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
                                ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                    <h2 class="text-2xl font-bold mb-4 text-blue-900"><?php echo e(__('Характеристики автомобиля')); ?></h2>
                                    <?php if($auctionEndsAtIso): ?>
                                        <div class="bg-white border border-dashed border-blue-200 rounded-lg p-4 mb-5"
                                             data-countdown
                                             data-expires="<?php echo e($auctionEndsAtIso); ?>"
                                             data-prefix="<?php echo e(__('До конца')); ?>"
                                             data-expired-text="<?php echo e(__('Аукцион завершён')); ?>"
                                             data-day-label="<?php echo e(__('д')); ?>">
                                            <div class="text-xs text-blue-800 fw-semibold text-uppercase mb-1">
                                                <?php echo e(__('Аукцион завершается')); ?>

                                            </div>
                                            <div class="text-2xl fw-bold text-blue-900" data-countdown-text><?php echo e(__('Загрузка…')); ?></div>
                                            <?php if($auctionEndsAtText): ?>
                                                <div class="text-sm text-muted mt-1">
                                                    <?php echo e(__('Ожидаемая дата: :date (:tz)', ['date' => $auctionEndsAtText, 'tz' => config('app.timezone')])); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php if($vehicle->make): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Марка:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($vehicle->make); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->model): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Модель:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($vehicle->model); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->year): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Год выпуска:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($vehicle->year); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-gray-600 font-medium"><?php echo e(__('Пробег:')); ?></span>
                                            <strong class="text-gray-900"><?php echo e($mileageText); ?></strong>
                                        </div>
                                        <?php if($vehicle->transmission): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Коробка передач:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($transmissionMap[$vehicle->transmission] ?? $vehicle->transmission); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->fuel_type): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Тип топлива:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($fuelTypeMap[$vehicle->fuel_type] ?? $vehicle->fuel_type); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->body_type): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Тип кузова:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($bodyTypeMap[$vehicle->body_type] ?? $vehicle->body_type); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->engine_displacement_cc): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Объём двигателя:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e(number_format($vehicle->engine_displacement_cc, 0, '.', ' ')); ?> <?php echo e(__('см³')); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($vehicle->exterior_color): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Цвет:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($vehicle->exterior_color); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($auctionEndsAtText): ?>
                                            <div class="flex justify-between border-b pb-2">
                                                <span class="text-gray-600 font-medium"><?php echo e(__('Окончание аукциона:')); ?></span>
                                                <strong class="text-gray-900"><?php echo e($auctionEndsAtText); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endif; ?>
                        </div>

                        <aside class="seller-card lg:row-span-2">
                            <div class="seller-card__header">
                                <img src="<?php echo e($sellerAvatar); ?>" alt="<?php echo e($seller->name ?? __('Продавец')); ?>" class="seller-card__avatar">
                                <div>
                                    <p class="seller-card__title"><?php echo e($seller->name ?? __('Продавец')); ?></p>
                                    <?php if($sellerJoined): ?>
                                        <p class="seller-card__muted"><?php echo e(__('На сервисе с :date', ['date' => $sellerJoined])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="seller-card__section">
                                <p class="seller-card__label"><?php echo e(__('Номер телефона')); ?></p>
                                <?php if($sellerPhone && $sellerPhoneVerified): ?>
                                    <div class="seller-card__phone">
                                        <a href="tel:<?php echo e($telHref); ?>" id="sellerPhoneValue"><?php echo e($sellerPhone); ?></a>
                                        <button type="button" class="seller-card__copy" data-copy-target="#sellerPhoneValue">
                                            <i class="fa-solid fa-copy"></i> <?php echo e(__('Скопировать')); ?>

                                        </button>
                                    </div>
                                    <p class="seller-card__muted"><?php echo e(__('Номер подтверждён через SMS')); ?></p>
                                <?php else: ?>
                                    <p class="seller-card__muted"><?php echo e(__('Телефон не указан')); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="seller-card__actions">
                                <a href="#contactSeller" class="btn btn-brand-gradient w-100">
                                    <i class="fa-solid fa-message me-2"></i>
                                    <?php echo e(__('Написать продавцу')); ?>

                                </a>
                            </div>
                        </aside>

                        <div class="space-y-8">
                            <?php if($listing->customFieldValues->isNotEmpty()): ?>
                                <div>
                                    <h4 class="text-xl font-bold mb-4"><?php echo e(__('Характеристики')); ?></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                                        <?php $__currentLoopData = $listing->customFieldValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <span class="text-gray-600"><?php echo e($customValue->field->name ?? __('Неизвестное поле')); ?>:</span>
                                                <strong class="text-gray-900 ml-2"><?php echo e($customValue->value); ?></strong>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(auth()->guard()->check()): ?>
                                <div class="border-t pt-6" id="contactSeller">
                                    <h3 class="text-xl font-bold mb-4"><?php echo e(__('Связаться с продавцом')); ?></h3>
                                    <?php if(auth()->id() === $listing->user_id): ?>
                                        <p class="text-gray-500"><?php echo e(__('Это ваше объявление.')); ?></p>
                                    <?php else: ?>
                                        <?php if(session('success_message')): ?>
                                            <div class="mb-4 text-green-600 font-semibold"><?php echo e(session('success_message')); ?></div>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('listings.messages.store', $listing)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <div>
                                                <textarea name="body" rows="4" class="w-full border-gray-300 rounded-md" placeholder="<?php echo e(__('Напишите ваше сообщение...')); ?>" required minlength="10"></textarea>
                                                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <div class="mt-4">
                                                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Отправить сообщение')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="border-t pt-6" id="contactSeller">
                                    <h3 class="text-xl font-bold mb-4"><?php echo e(__('Связаться с продавцом')); ?></h3>
                                    <p class="text-gray-500 mb-4"><?php echo e(__('Войдите в аккаунт, чтобы написать продавцу.')); ?></p>
                                    <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-secondary"><?php echo e(__('Войти')); ?></a>
                                </div>
                            <?php endif; ?>

                            <div class="border-t pt-6">
                                <h3 class="text-xl font-bold mb-4"><?php echo e(__('Отзывы о продавце')); ?></h3>
                                <div class="space-y-4">
                                    <?php $__empty_1 = true; $__currentLoopData = $listing->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="border-b pb-2">
                                            <div class="flex items-center mb-1">
                                                <span class="font-semibold"><?php echo e($review->reviewer->name ?? __('Анонимный пользователь')); ?></span>
                                                <div class="ml-2 flex text-yellow-400">
                                                    <?php for($i = 0; $i < $review->rating; $i++): ?>
                                                        ★
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <p class="text-gray-700"><?php echo e($review->comment); ?></p>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo e($review->created_at->format('d.m.Y')); ?></p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <p class="text-gray-500"><?php echo e(__('Отзывов пока нет.')); ?></p>
                                    <?php endif; ?>
                                </div>

                                <?php if(auth()->guard()->check()): ?>
                                    <?php if(auth()->id() !== $listing->user_id && !$listing->reviews->contains('reviewer_id', auth()->id())): ?>
                                        <div class="mt-6">
                                            <h4 class="text-lg font-semibold mb-2"><?php echo e(__('Оставить отзыв')); ?></h4>
                                            <form action="<?php echo e(route('listings.reviews.store', $listing)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="rating" value="0">
                                                <div class="flex items-center space-x-1 text-gray-400 flex-row-reverse justify-end">
                                                    <?php for($i=5; $i>=1; $i--): ?>
                                                        <input type="radio" name="rating" value="<?php echo e($i); ?>" class="hidden peer" id="rate-<?php echo e($i); ?>">
                                                        <label for="rate-<?php echo e($i); ?>" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                                    <?php endfor; ?>
                                                    <label class="mr-2"><?php echo e(__('Оценка:')); ?></label>
                                                </div>
                                                <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                                <div class="mt-4">
                                                    <textarea name="comment" rows="4" class="w-full border-gray-300 rounded-md" placeholder="<?php echo e(__('Напишите ваш отзыв...')); ?>" required minlength="10"></textarea>
                                                    <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>

                                                <div class="mt-4">
                                                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e(__('Отправить отзыв')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <?php if($relatedListings->isNotEmpty()): ?>
                                <div class="border-t pt-6">
                                    <h3 class="text-2xl font-bold mb-6"><?php echo e(__('Похожие объявления')); ?></h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <?php $__currentLoopData = $relatedListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $relatedImage = $item->getFirstMediaUrl('images', 'medium')
                                                    ?: $item->getFirstMediaUrl('images')
                                                    ?: $item->getFirstMediaUrl('auction_photos', 'medium')
                                                    ?: $item->getFirstMediaUrl('auction_photos')
                                                    ?: $item->vehicleDetail->preview_image_url
                                                    ?? $item->vehicleDetail->main_image_url
                                                    ?? asset('images/no-image.jpg');
                                            ?>
                                            <a href="<?php echo e(route('listings.show', $item)); ?>" class="block bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                                                <div class="bg-gray-100" style="aspect-ratio: 4 / 3;">
                                                    <img src="<?php echo e($relatedImage); ?>" alt="<?php echo e($item->title); ?>" class="w-full h-full object-cover" onerror="this.src='<?php echo e(asset('images/no-image.jpg')); ?>'">
                                                </div>
                                                <div class="p-3">
                                                    <h4 class="font-semibold text-gray-900 line-clamp-1"><?php echo e($item->title); ?></h4>
                                                    <p class="text-xs text-gray-500 mb-2"><?php echo e($item->region?->localized_name ?? __('Без региона')); ?></p>
                                                    <p class="text-indigo-600 font-bold text-sm"><?php echo e(number_format($item->price, 0, '.', ' ')); ?> <?php echo e($item->currency); ?></p>
                                                </div>
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php if (! $__env->hasRenderedOnce('130bb6d8-35bf-466c-b08f-3d5b8c10404a')): $__env->markAsRenderedOnce('130bb6d8-35bf-466c-b08f-3d5b8c10404a'); ?>
        <?php $__env->startPush('styles'); ?>
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
        <?php $__env->stopPush(); ?>

        <?php $__env->startPush('scripts'); ?>
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
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/listings/show.blade.php ENDPATH**/ ?>
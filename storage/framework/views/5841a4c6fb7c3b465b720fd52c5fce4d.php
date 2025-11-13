<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'listing',
    'badge' => null,
    'expires' => null,
    'expired' => null,
    'showFavorite' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'listing',
    'badge' => null,
    'expires' => null,
    'expired' => null,
    'showFavorite' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if (!$expires && isset($listing->auction_ends_at)) {
        $expires = $listing->auction_ends_at;
    }

    $endsAt = $expires instanceof \Carbon\Carbon ? $expires : ($expires ? \Carbon\Carbon::parse($expires) : null);
    $isExpired = $expired ?? ($endsAt ? $endsAt->isPast() : false);
    $remainingLabel = $endsAt && !$isExpired
        ? $endsAt->diffForHumans(now(), true, false, 2)
        : null;
    $expiresIso = $endsAt?->toIso8601String();
?>

<?php
    static $inlineFallback = null;

    if ($inlineFallback === null) {
        $fallbackCandidates = [
            public_path('images/no-image.jpg') => 'image/jpeg',
            public_path('images/no-image.svg') => 'image/svg+xml',
        ];

        foreach ($fallbackCandidates as $path => $mime) {
            if (is_readable($path)) {
                $inlineFallback = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                break;
            }
        }

        if ($inlineFallback === null) {
            $svgText = __('Нет фото');
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#e5e7eb"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-family="Arial" font-size="22">'.$svgText.'</text></svg>';
            $inlineFallback = 'data:image/svg+xml;base64,' . base64_encode($svg);
        }
    }

    $fallbackImage = $inlineFallback;
    $photoSources = [];

    $buildMediaUrl = function ($media) {
        $conversion = $media->hasGeneratedConversion('medium') ? 'medium' : null;
        return $conversion
            ? route('media.show', ['media' => $media->id, 'conversion' => $conversion])
            : route('media.show', ['media' => $media->id]);
    };

    $addMediaCollection = function ($mediaItems) use (&$photoSources, $buildMediaUrl) {
        foreach ($mediaItems as $media) {
            $photoSources[] = $buildMediaUrl($media);
        }
    };

    if (!$listing->relationLoaded('media')) {
        $listing->loadMissing('media');
    }

    $addMediaCollection($listing->getMedia('images'));
    $addMediaCollection($listing->getMedia('auction_photos'));

    $photoSources = array_slice(array_values(array_filter(array_unique($photoSources))), 0, 12);

    if (empty($photoSources) && $listing->vehicleDetail) {
        $previewUrl = $listing->vehicleDetail->preview_image_url
            ?? $listing->vehicleDetail->main_image_url
            ?? null;

        if ($previewUrl) {
            if (\Illuminate\Support\Str::startsWith($previewUrl, '/')) {
                $previewUrl = rtrim(config('app.url'), '/') . $previewUrl;
            }
            $photoSources[] = $previewUrl;
        }
    }

    if (empty($photoSources)) {
        $photoSources[] = $fallbackImage;
    }

    $preview = $photoSources[0];
    $vehicleDetail = $listing->vehicleDetail ?? null;
    $buyNowPrice = $vehicleDetail?->buy_now_price;
    $buyNowCurrency = $vehicleDetail?->buy_now_currency ?: $listing->currency;
    $operationalStatus = $vehicleDetail?->operational_status;
    $isBuyNowAvailable = $buyNowPrice !== null;
    $currentBidPrice = $vehicleDetail?->current_bid_price;
    $currentBidCurrency = $vehicleDetail?->current_bid_currency ?: $buyNowCurrency ?: $listing->currency;
    $displayPrice = $isBuyNowAvailable ? $buyNowPrice : ($listing->price ?? null);
    $displayCurrency = $isBuyNowAvailable
        ? ($buyNowCurrency ?? 'USD')
        : ($listing->currency ?? $buyNowCurrency ?? 'USD');
    $isBuyNowPrimary = $isBuyNowAvailable && $displayPrice === $buyNowPrice;
?>

<div class="brand-listing-card<?php echo e($isBuyNowAvailable ? ' brand-listing-card--buy-now' : ''); ?>">

    <div class="brand-listing-card__media" data-photo-sources='<?php echo json_encode($photoSources, 15, 512) ?>'>
        <a href="<?php echo e(route('listings.show', $listing)); ?>">
            <img
                src="<?php echo e($preview); ?>"
                alt="<?php echo e($listing->title); ?>"
                loading="lazy"
                onerror="this.src='<?php echo e($fallbackImage); ?>'"
            >
        </a>

        <?php if($badge): ?>
            <div class="brand-listing-card__badge"><?php echo e($badge); ?></div>
        <?php endif; ?>

        <?php if($endsAt): ?>
            <div
                class="brand-listing-card__timer"
                data-countdown
                data-expires="<?php echo e($expiresIso); ?>"
                data-prefix="<?php echo e(__('Осталось')); ?>"
                data-expired-text="<?php echo e(__('Лот завершён')); ?>"
            >
                <span data-countdown-text>
                    <?php if($isExpired): ?>
                        <?php echo e(__('Лот завершён')); ?>

                    <?php else: ?>
                        <?php echo e(__('Осталось: :time', ['time' => $remainingLabel])); ?>

                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if($showFavorite): ?>
            <?php if(auth()->guard()->check()): ?>
                <form action="<?php echo e(route('listings.favorite.toggle', $listing)); ?>" method="POST" class="brand-listing-card__favorite">
                    <?php echo csrf_field(); ?>
                    <button type="submit">
                        <?php if(auth()->user()->favorites->contains($listing)): ?>
                            <i class="fa-solid fa-heart"></i>
                        <?php else: ?>
                            <i class="fa-regular fa-heart"></i>
                        <?php endif; ?>
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <a href="<?php echo e(route('listings.show', $listing)); ?>" class="text-decoration-none">
        <div class="brand-listing-card__content">
            <h4 class="brand-listing-card__title"><?php echo e($listing->title); ?></h4>
            <p class="brand-listing-card__meta"><?php echo e($listing->region?->localized_name ?? __('Регион не указан')); ?></p>
            <p class="brand-listing-card__price">
                <?php if($isBuyNowPrimary): ?>
                    <span class="brand-listing-card__price-label"><?php echo e(__('Купить сейчас')); ?></span>
                <?php endif; ?>
                <span class="brand-listing-card__price-value">
                    <?php echo e($displayPrice !== null ? number_format($displayPrice, 0, '.', ' ') . ' ' . $displayCurrency : __('Цена уточняется')); ?>

                </span>
            </p>
            <?php if($currentBidPrice !== null): ?>
                <div class="brand-listing-card__bid-line">
                    <span class="brand-listing-card__bid-label"><?php echo e(__('Текущая ставка')); ?></span>
                    <span class="brand-listing-card__bid-value">
                        <?php echo e(number_format($currentBidPrice, 0, '.', ' ')); ?> <?php echo e($currentBidCurrency ?? $displayCurrency); ?>

                    </span>
                </div>
            <?php endif; ?>
            <?php if($operationalStatus): ?>
                <p class="brand-listing-card__status"><?php echo e(__('Состояние:')); ?> <?php echo e($operationalStatus); ?></p>
            <?php endif; ?>
        </div>
    </a>
</div>
<?php /**PATH /var/www/html/resources/views/components/listing/card.blade.php ENDPATH**/ ?>
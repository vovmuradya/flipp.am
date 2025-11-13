@props([
    'listing',
    'badge' => null,
    'expires' => null,
    'expired' => null,
    'showFavorite' => false,
])

@php
    if (!$expires && isset($listing->auction_ends_at)) {
        $expires = $listing->auction_ends_at;
    }

    $endsAt = $expires instanceof \Carbon\Carbon ? $expires : ($expires ? \Carbon\Carbon::parse($expires) : null);
    $isExpired = $expired ?? ($endsAt ? $endsAt->isPast() : false);
    $remainingLabel = $endsAt && !$isExpired
        ? $endsAt->diffForHumans(now(), true, false, 2)
        : null;
    $expiresIso = $endsAt?->toIso8601String();
@endphp

@php
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
@endphp

<div class="brand-listing-card{{ $isBuyNowAvailable ? ' brand-listing-card--buy-now' : '' }}">

    <div class="brand-listing-card__media" data-photo-sources='@json($photoSources)'>
        <a href="{{ route('listings.show', $listing) }}">
            <img
                src="{{ $preview }}"
                alt="{{ $listing->title }}"
                loading="lazy"
                onerror="this.src='{{ $fallbackImage }}'"
            >
        </a>

        @if($badge)
            <div class="brand-listing-card__badge">{{ $badge }}</div>
        @endif

        @if($endsAt)
            <div
                class="brand-listing-card__timer"
                data-countdown
                data-expires="{{ $expiresIso }}"
                data-prefix="{{ __('Осталось') }}"
                data-expired-text="{{ __('Лот завершён') }}"
            >
                <span data-countdown-text>
                    @if($isExpired)
                        {{ __('Лот завершён') }}
                    @else
                        {{ __('Осталось: :time', ['time' => $remainingLabel]) }}
                    @endif
                </span>
            </div>
        @endif

        @if($showFavorite)
            @auth
                <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST" class="brand-listing-card__favorite">
                    @csrf
                    <button type="submit">
                        @if(auth()->user()->favorites->contains($listing))
                            <i class="fa-solid fa-heart"></i>
                        @else
                            <i class="fa-regular fa-heart"></i>
                        @endif
                    </button>
                </form>
            @endauth
        @endif
    </div>

    <a href="{{ route('listings.show', $listing) }}" class="text-decoration-none">
        <div class="brand-listing-card__content">
            <h4 class="brand-listing-card__title">{{ $listing->title }}</h4>
            <p class="brand-listing-card__meta">{{ $listing->region?->localized_name ?? __('Регион не указан') }}</p>
            <p class="brand-listing-card__price">
                @if($isBuyNowPrimary)
                    <span class="brand-listing-card__price-label">{{ __('Купить сейчас') }}</span>
                @endif
                <span class="brand-listing-card__price-value">
                    {{ $displayPrice !== null ? number_format($displayPrice, 0, '.', ' ') . ' ' . $displayCurrency : __('Цена уточняется') }}
                </span>
            </p>
            @if($currentBidPrice !== null)
                <div class="brand-listing-card__bid-line">
                    <span class="brand-listing-card__bid-label">{{ __('Текущая ставка') }}</span>
                    <span class="brand-listing-card__bid-value">
                        {{ number_format($currentBidPrice, 0, '.', ' ') }} {{ $currentBidCurrency ?? $displayCurrency }}
                    </span>
                </div>
            @endif
            @if($operationalStatus)
                <p class="brand-listing-card__status">{{ __('Состояние:') }} {{ $operationalStatus }}</p>
            @endif
        </div>
    </a>
</div>

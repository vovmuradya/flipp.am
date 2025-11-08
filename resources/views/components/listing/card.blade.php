@props([
    'listing',
    'badge' => null,
    'expires' => null,
    'expired' => null,
    'showFavorite' => false,
])

@php
    $endsAt = $expires instanceof \Carbon\Carbon ? $expires : ($expires ? \Carbon\Carbon::parse($expires) : null);
    $isExpired = $expired ?? ($endsAt ? $endsAt->isPast() : false);
    $remainingLabel = $endsAt && !$isExpired
        ? $endsAt->diffForHumans(now(), true, false, 2)
        : null;
    $expiresIso = $endsAt?->toIso8601String();
    @endphp

<div class="brand-listing-card">
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
    @endphp

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
            <p class="brand-listing-card__meta">{{ $listing->region?->name ?? __('Регион не указан') }}</p>
            <p class="brand-listing-card__price">
                {{ number_format($listing->price, 0, '.', ' ') }} {{ $listing->currency }}
            </p>
        </div>
    </a>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const initialized = new WeakMap();
                const localeStrings = {
                    expired: @json(__('Лот завершён')),
                    prefix: @json(__('Осталось')),
                    dayLabel: @json(__('д')),
                };

                function pad(value) {
                    return String(value).padStart(2, '0');
                }

                function formatRemaining(diffMillis, dayLabel) {
                    if (diffMillis <= 0) {
                        return null;
                    }

                    const totalSeconds = Math.floor(diffMillis / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    if (days > 0) {
                        return `${days}${dayLabel} ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                    }

                    const totalHours = Math.floor(totalSeconds / 3600);
                    return `${pad(totalHours)}:${pad(minutes)}:${pad(seconds)}`;
                }

                function updateCountdown(el) {
                    const meta = initialized.get(el);
                    if (!meta) {
                        return;
                    }

                    const { endTs, textNode, expiredText, prefix, dayLabel } = meta;
                    const now = Date.now();
                    const remaining = endTs - now;

                    if (remaining <= 0) {
                        textNode.textContent = expiredText || localeStrings.expired;
                        el.dataset.countdownState = 'expired';
                        return;
                    }

                    const formatted = formatRemaining(remaining, dayLabel);
                    textNode.textContent = formatted
                        ? `${prefix ? prefix + ': ' : ''}${formatted}`
                        : (expiredText || localeStrings.expired);
                }

                function initCountdown(el) {
                    if (initialized.has(el)) {
                        return;
                    }

                    const expires = el.dataset.expires;
                    if (!expires) {
                        return;
                    }

                    const endTs = Date.parse(expires);
                    if (Number.isNaN(endTs)) {
                        return;
                    }

                    const textNode = el.querySelector('[data-countdown-text]');
                    if (!textNode) {
                        return;
                    }

                    initialized.set(el, {
                        endTs,
                        textNode,
                        expiredText: el.dataset.expiredText || localeStrings.expired,
                        prefix: el.dataset.prefix ?? localeStrings.prefix,
                        dayLabel: el.dataset.dayLabel || localeStrings.dayLabel,
                    });

                    updateCountdown(el);
                }

                function boot() {
                    const all = document.querySelectorAll('[data-countdown]');
                    all.forEach(initCountdown);

                    if (!window.__listingCountdownInterval) {
                        window.__listingCountdownInterval = setInterval(() => {
                            document.querySelectorAll('[data-countdown]').forEach(updateCountdown);
                        }, 1000);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot);
                } else {
                    boot();
                }
            })();
        </script>
    @endpush
@endonce

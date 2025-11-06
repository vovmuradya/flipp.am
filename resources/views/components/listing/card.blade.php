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
        $fallbackImage = asset('images/no-image.svg');
        $photoSources = [];

        $addMediaCollection = function ($mediaItems) use (&$photoSources) {
            foreach ($mediaItems as $media) {
                $photoSources[] = $media->hasGeneratedConversion('medium')
                    ? $media->getFullUrl('medium')
                    : $media->getFullUrl();
            }
        };

        if ($listing->relationLoaded('media')) {
            $addMediaCollection($listing->getMedia('images'));
            $addMediaCollection($listing->getMedia('auction_photos'));
        } else {
            $addMediaCollection($listing->loadMissing('media')->getMedia('images'));
            $addMediaCollection($listing->getMedia('auction_photos'));
        }

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
                data-prefix="Осталось"
                data-expired-text="Лот завершён"
            >
                <span data-countdown-text>
                    @if($isExpired)
                        Лот завершён
                    @else
                        Осталось: {{ $remainingLabel }}
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
            <p class="brand-listing-card__meta">{{ $listing->region?->name ?? 'Регион не указан' }}</p>
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

                function pad(value) {
                    return String(value).padStart(2, '0');
                }

                function formatRemaining(diffMillis) {
                    if (diffMillis <= 0) {
                        return null;
                    }

                    const totalSeconds = Math.floor(diffMillis / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    if (days > 0) {
                        return `${days}д ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                    }

                    const totalHours = Math.floor(totalSeconds / 3600);
                    return `${pad(totalHours)}:${pad(minutes)}:${pad(seconds)}`;
                }

                function updateCountdown(el) {
                    const meta = initialized.get(el);
                    if (!meta) {
                        return;
                    }

                    const { endTs, textNode, expiredText, prefix } = meta;
                    const now = Date.now();
                    const remaining = endTs - now;

                    if (remaining <= 0) {
                        textNode.textContent = expiredText || 'Лот завершён';
                        el.dataset.countdownState = 'expired';
                        return;
                    }

                    const formatted = formatRemaining(remaining);
                    textNode.textContent = formatted
                        ? `${prefix ? prefix + ': ' : ''}${formatted}`
                        : (expiredText || 'Лот завершён');
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
                        expiredText: el.dataset.expiredText || 'Лот завершён',
                        prefix: el.dataset.prefix || ''
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

            (function () {
                const initialized = new WeakSet();

                function parsePhotos(data) {
                    try {
                        const parsed = JSON.parse(data);
                        return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
                    } catch (error) {
                        return [];
                    }
                }

                function initCarousel(container) {
                    if (initialized.has(container)) {
                        return;
                    }

                    const sources = parsePhotos(container.dataset.photoSources || '[]');
                    if (sources.length <= 1) {
                        return;
                    }

                    const image = container.querySelector('img');
                    if (!image) {
                        return;
                    }

                    let currentIndex = 0;

                    const showIndex = (index) => {
                        if (!sources[index] || currentIndex === index) {
                            return;
                        }
                        currentIndex = index;
                        image.src = sources[index];
                    };

                    container.addEventListener('mousemove', (event) => {
                        const rect = container.getBoundingClientRect();
                        if (!rect.width) {
                            return;
                        }
                        const relativeX = Math.min(Math.max(event.clientX - rect.left, 0), rect.width);
                        const ratio = relativeX / rect.width;
                        const targetIndex = Math.min(sources.length - 1, Math.floor(ratio * sources.length));
                        showIndex(targetIndex);
                    });

                    container.addEventListener('mouseleave', () => {
                        showIndex(0);
                    });

                    initialized.add(container);
                }

                function boot() {
                    document.querySelectorAll('[data-photo-sources]').forEach(initCarousel);
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

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

<div class="border border-gray-200 rounded-lg shadow-sm overflow-hidden group relative">
    <div class="relative">
        <a href="{{ route('listings.show', $listing) }}">
            <img src="{{ $listing->getPreviewUrl('medium') }}"
                 alt="{{ $listing->title }}"
                 class="w-full h-48 object-cover"
                 onerror="this.src='https://placehold.co/400x300/e5e7eb/6b7280?text=Нет+фото'">
        </a>

        @if($badge)
            <div class="absolute top-2 right-2 bg-white text-blue-600 text-xs font-semibold px-2 py-1 rounded-full shadow">
                {{ $badge }}
            </div>
        @endif

        @if($endsAt)
            <div
                class="absolute top-2 left-2 bg-blue-900 bg-opacity-80 text-white text-xs px-2 py-1 rounded"
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
                <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST" class="absolute top-2 right-2">
                    @csrf
                    <button type="submit" class="p-2 rounded-full bg-white/80 backdrop-blur-sm hover:bg-white">
                        @if(auth()->user()->favorites->contains($listing))
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        @endif
                    </button>
                </form>
            @endauth
        @endif
    </div>

    <a href="{{ route('listings.show', $listing) }}">
        <div class="p-4 bg-white">
            <h4 class="font-bold text-lg truncate text-gray-900">{{ $listing->title }}</h4>
            <p class="text-sm text-gray-600 mt-1">{{ $listing->region?->name }}</p>
            <p class="text-xl font-semibold mt-3 text-blue-600">
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
                            const targets = document.querySelectorAll('[data-countdown]');
                            targets.forEach(updateCountdown);
                        }, 1000);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot);
                } else {
                    boot();
                }

                const observer = new MutationObserver((mutations) => {
                    for (const mutation of mutations) {
                        mutation.addedNodes.forEach((node) => {
                            if (!(node instanceof HTMLElement)) {
                                return;
                            }
                            if (node.matches && node.matches('[data-countdown]')) {
                                initCountdown(node);
                            }
                            node.querySelectorAll?.('[data-countdown]').forEach(initCountdown);
                        });
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    @endpush
@endonce

<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Авто с аукционов Copart') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Просматривайте и отслеживайте все импортированные лоты. Следите за статусом и переходите к подробному описанию в один клик.') }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h3 class="h5 fw-semibold mb-0">{{ __('Найдено :count лотов', ['count' => $listings->total()]) }}</h3>
                <a href="{{ route('listings.create-from-auction') }}" class="btn btn-brand-outline">
                    + {{ __('Добавить из Copart') }}
                </a>
            </div>

            <div class="row gy-4">
                @forelse($listings as $listing)
                    @php
                        $endsAt = optional($listing->vehicleDetail)->auction_ends_at;
                        $endsAtLocal = $endsAt?->timezone(config('app.timezone'));
                        $endsAtIso = $endsAtLocal?->toIso8601String();
                        $statusLabel = __('Active');
                        $statusClass = 'bg-success-subtle text-success-emphasis';

                        if ($endsAt instanceof \Carbon\Carbon) {
                            if ($endsAt->isPast()) {
                                $statusLabel = __('Ended');
                                $statusClass = 'bg-secondary-subtle text-secondary-emphasis';
                            } elseif ($endsAt->diffInHours(now()) >= 24) {
                                $statusLabel = __('Upcoming');
                                $statusClass = 'bg-warning-subtle text-warning-emphasis';
                            } else {
                                $statusLabel = __('Active');
                                $statusClass = 'bg-success-subtle text-success-emphasis';
                            }
                        }

                        $resolveMediaUrl = function ($media, $preferredConversion = 'medium') {
                            if (!$media || !method_exists($media, 'getKey')) {
                                return null;
                            }

                            $params = ['media' => $media->getKey()];
                            if ($preferredConversion && method_exists($media, 'hasGeneratedConversion')
                                && $media->hasGeneratedConversion($preferredConversion)) {
                                $params['conversion'] = $preferredConversion;
                            }

                            try {
                                return route('media.show', $params);
                            } catch (\Throwable $e) {
                                try {
                                    return $preferredConversion
                                        ? $media->getUrl($preferredConversion)
                                        : $media->getUrl();
                                } catch (\Throwable $_) {
                                    return null;
                                }
                            }
                        };

                        $fallbackImage = asset('images/no-image.jpg');
                        $previewImage = $resolveMediaUrl($listing->getFirstMedia('images'))
                            ?: $resolveMediaUrl($listing->getFirstMedia('auction_photos'))
                            ?: $listing->getFirstMediaUrl('images')
                            ?: $listing->getFirstMediaUrl('auction_photos');

                        if (!$previewImage && $listing->image) {
                            $rawPath = $listing->image;
                            if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
                                $previewImage = $rawPath;
                            } else {
                                $normalized = ltrim(str_replace('public/', '', $rawPath), '/');
                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($normalized)) {
                                    $previewImage = \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
                                } elseif (\Illuminate\Support\Facades\Storage::exists($rawPath)) {
                                    $previewImage = \Illuminate\Support\Facades\Storage::url($rawPath);
                                }
                            }
                        }

                        if (!$previewImage && $listing->vehicleDetail) {
                            foreach ([
                                $listing->vehicleDetail->preview_image_url,
                                $listing->vehicleDetail->main_image_url,
                            ] as $external) {
                                if (is_string($external) && trim($external) !== '') {
                                    $previewImage = trim($external);
                                    break;
                                }
                            }
                        }

                        if (!$previewImage && !empty($listing->auction_photo_urls)) {
                            $previewImage = collect(is_array($listing->auction_photo_urls)
                                ? $listing->auction_photo_urls
                                : json_decode($listing->auction_photo_urls, true)
                            )
                                ->filter(fn ($url) => is_string($url) && trim($url) !== '')
                                ->first();
                        }

                        $previewImage = $previewImage ?: $fallbackImage;
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="auction-card" id="auction-card-{{ $listing->id }}" data-listing-card="{{ $listing->id }}">
                            <span class="auction-card__badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            <a href="{{ route('listings.show', $listing) }}" class="auction-card__image">
                                <img src="{{ $previewImage }}" alt="{{ $listing->title }}" loading="lazy" onerror="this.src='{{ $fallbackImage }}'">
                            </a>
                            <div class="auction-card__body">
                                <div class="auction-card__heading">
                                    <h5 title="{{ $listing->title }}">{{ $listing->title }}</h5>
                                    <span class="auction-card__lot">{{ __('Лот № :id', ['id' => $listing->id]) }}</span>
                                </div>
                                <ul class="auction-card__meta">
                                    <li>
                                        <span>{{ __('Окончание') }}</span>
                                        @if($endsAtIso)
                                            <strong class="d-flex flex-column align-items-end gap-1 text-end">
                                                <span
                                                    data-countdown
                                                    data-expires="{{ $endsAtIso }}"
                                                    data-prefix="{{ __('До конца') }}"
                                                    data-expired-text="{{ __('Лот завершён') }}"
                                                    data-day-label="{{ __('д') }}"
                                                >
                                                    <span data-countdown-text>{{ __('Загрузка…') }}</span>
                                                </span>
                                                <small class="text-muted">{{ $endsAtLocal->format('d.m.Y H:i') }}</small>
                                            </strong>
                                        @else
                                            <strong>{{ __('Не указано') }}</strong>
                                        @endif
                                    </li>
                                    <li>
                                        <span>{{ __('Ставка') }}</span>
                                        <strong>{{ number_format($listing->price, 0, '.', ' ') }} {{ $listing->currency }}</strong>
                                    </li>
                                </ul>
                                <div class="auction-card__actions">
                                    <a href="{{ route('listings.show', $listing) }}" class="btn btn-brand-gradient">
                                        {{ __('Подробнее') }}
                                    </a>
                                    @if(auth()->id() === $listing->user_id)
                                        <a href="{{ route('auction-listings.edit', $listing) }}" class="btn btn-outline-secondary">
                                            {{ __('Редактировать') }}
                                        </a>
                                        <form action="{{ route('auction-listings.destroy', $listing) }}"
                                              method="POST"
                                              data-auction-delete
                                              data-listing-card="auction-card-{{ $listing->id }}"
                                              data-confirm="{{ __('Удалить это объявление?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                {{ __('Удалить') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="brand-surface text-center py-5 text-muted">
                        {{ __('По заданным параметрам ничего не найдено. Попробуйте изменить фильтры.') }}
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $listings->links() }}
            </div>
        </div>
    </section>
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.querySelectorAll('[data-auction-delete]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.pending === 'true') {
                        event.preventDefault();
                        return;
                    }

                    const message = form.dataset.confirm || '';
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                        return;
                    }

                    if (!window.fetch || !token) {
                        return;
                    }

                    event.preventDefault();
                    form.dataset.pending = 'true';

                    const payload = new URLSearchParams(new FormData(form)).toString();

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        },
                        body: payload,
                    }).then((response) => {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }

                        const cardId = form.dataset.listingCard;
                        const card = cardId ? document.getElementById(cardId) : form.closest('[data-listing-card]');
                        if (card) {
                            card.classList.add('auction-card--removed');
                            setTimeout(() => card.remove(), 250);
                        }
                    }).catch(() => {
                        delete form.dataset.pending;
                        form.submit();
                    });
                });
            });
        });
    </script>
@endpush

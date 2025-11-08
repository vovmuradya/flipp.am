<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Мои аукционные объявления') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Управляйте объявлениями, импортированными с Copart: обновляйте информацию, отслеживайте статус и создавайте новые лоты.') }}
                </p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="brand-surface">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="h5 fw-semibold mb-1">{{ __('Список ваших лотов') }}</h3>
                        <p class="mb-0 text-muted">{{ __('Все объявления, которые вы импортировали с Copart.') }}</p>
                    </div>
                    <a href="{{ route('listings.create-from-auction') }}" class="btn btn-brand-gradient">
                        {{ __('Добавить новое') }}
                    </a>
                </div>

                @if($listings->isEmpty())
                    <div class="text-center py-5" style="border: 1px dashed rgba(18,18,18,0.08); border-radius: 18px;">
                        <div class="mb-3" style="font-size: 32px;">🗂️</div>
                        <h4 class="fw-semibold">{{ __('Пока нет импортированных объявлений') }}</h4>
                        <p class="text-muted mb-4">{{ __('Импортируйте лот с Copart — фотографии и характеристики подтянутся автоматически.') }}</p>
                        <a href="{{ route('listings.create-from-auction') }}" class="btn btn-brand-gradient">
                            {{ __('Импортировать с Copart') }}
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                            <tr>
                                <th scope="col">{{ __('Фото') }}</th>
                                <th scope="col">{{ __('Заголовок') }}</th>
                                <th scope="col">{{ __('Цена') }}</th>
                                <th scope="col">{{ __('Статус') }}</th>
                                <th scope="col" class="text-end">{{ __('Действия') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($listings as $listing)
                                @php
                                    $endsAt = $listing->auction_ends_at;
                                    $isExpired = $endsAt ? $endsAt->isPast() : false;
                                    $remainingLabel = $endsAt && !$isExpired
                                        ? $endsAt->diffForHumans(now(), true, false, 2)
                                        : null;
                                    $expiresIso = $endsAt?->toIso8601String();
                                @endphp
                                <tr>
                                    <td style="width: 110px;">
                                        <div class="position-relative" style="width: 96px; height: 72px; border-radius: 12px; overflow: hidden; background: #f1f3f5;">
                                            @php
                                                $fallbackImage = asset('images/no-image.svg');
                                                $imageUrl = null;
                                                if ($listing->hasMedia('images')) {
                                                    $imageUrl = method_exists($listing, 'getPreviewUrl')
                                                        ? ($listing->getPreviewUrl('thumb') ?: $listing->getPreviewUrl())
                                                        : $listing->getFirstMedia('images')?->getUrl('thumb');
                                                }
                                                if (!$imageUrl) {
                                                    $imageUrl = optional($listing->vehicleDetail)->preview_image_url;
                                                }
                                                $imageUrl = $imageUrl ?: $fallbackImage;
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $listing->title }}" class="w-100 h-100" style="object-fit: cover;">
                                            @if($endsAt)
                                                <div
                                                    class="position-absolute top-0 start-0 m-1 px-2 py-1 rounded"
                                                    style="background: rgba(17,17,17,0.75); color: #fff; font-size: 10px;"
                                                    data-countdown
                                                    data-expires="{{ $expiresIso }}"
                                                    data-prefix="{{ __('До конца') }}"
                                                    data-expired-text="{{ __('Лот завершён') }}"
                                                >
                                                    <span data-countdown-text>
                                                        @if($isExpired)
                                                            {{ __('Лот завершён') }}
                                                        @else
                                                            {{ __('До конца: :time', ['time' => $remainingLabel]) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $listing->title }}</div>
                                        <div class="text-muted small">{{ $listing->created_at->format('d.m.Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($listing->price, 0, '.', ' ') }}</span>
                                        <span class="text-muted">{{ $listing->currency }}</span>
                                    </td>
                                    <td>
                                        @if($listing->status === 'active')
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">{{ __('Активно') }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">{{ __('Черновик') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-3">
                                            <a href="{{ route('auction-listings.edit', $listing) }}" class="text-decoration-none fw-semibold" style="color: var(--brand-orange);">
                                                {{ __('Редактировать') }}
                                            </a>
                                            <form action="{{ route('auction-listings.destroy', $listing) }}" method="POST" onsubmit="return confirm('{{ __('Удалить объявление?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0 text-decoration-none fw-semibold" style="color: var(--brand-red);">
                                                    {{ __('Удалить') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-3">
                        {{ $listings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app-layout>

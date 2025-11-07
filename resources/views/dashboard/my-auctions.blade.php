<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">Авто с аукционов Copart</h2>
                <p class="brand-section__subtitle">
                    Просматривайте и отслеживайте все импортированные лоты. Следите за статусом и переходите к подробному описанию в один клик.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h3 class="h5 fw-semibold mb-0">Найдено {{ $listings->total() }} лотов</h3>
                <a href="{{ route('listings.create-from-auction') }}" class="btn btn-brand-outline">
                    + Добавить из Copart
                </a>
            </div>

            <div class="row gy-4">
                @forelse($listings as $listing)
                    @php
                        $endsAt = optional($listing->vehicleDetail)->auction_ends_at;
                        $statusLabel = 'Active';
                        $statusClass = 'bg-success-subtle text-success-emphasis';

                        if ($endsAt instanceof \Carbon\Carbon) {
                            if ($endsAt->isPast()) {
                                $statusLabel = 'Ended';
                                $statusClass = 'bg-secondary-subtle text-secondary-emphasis';
                            } elseif ($endsAt->diffInHours(now()) >= 24) {
                                $statusLabel = 'Upcoming';
                                $statusClass = 'bg-warning-subtle text-warning-emphasis';
                            } else {
                                $statusLabel = 'Active';
                                $statusClass = 'bg-success-subtle text-success-emphasis';
                            }
                        }

                        $fallbackImage = asset('images/no-image.jpg');
                        $previewImage = $listing->getFirstMediaUrl('images', 'medium')
                            ?: $listing->getFirstMediaUrl('images')
                            ?: $listing->getFirstMediaUrl('auction_photos', 'medium')
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

                        $previewImage = $previewImage ?: $fallbackImage;
                    @endphp
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3 position-relative overflow-hidden h-100">
                            <span class="badge {{ $statusClass }} position-absolute top-0 end-0 m-3 px-3 py-2">
                                {{ $statusLabel }}
                            </span>
                            <div class="row g-0">
                                <div class="col-12 col-md-4">
                                    <div class="ratio ratio-4x3 bg-light overflow-hidden rounded-start rounded-top rounded-md-start">
                                        <a href="{{ route('listings.show', $listing) }}" class="d-flex w-100 h-100">
                                            <img src="{{ $previewImage }}"
                                                 alt="{{ $listing->title }}"
                                                 class="img-fluid w-100 h-100 object-fit-cover"
                                                 loading="lazy"
                                                 onerror="this.src='{{ $fallbackImage }}'">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="card-body d-flex flex-column h-100">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                                        <h5 class="card-title fs-5 mb-0 text-truncate" title="{{ $listing->title }}">
                                                {{ $listing->title }}
                                            </h5>
                                            <div class="text-muted small">
                                                Лот № {{ $listing->id }}
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-2">
                                            Окончание аукциона:
                                            @if($endsAt)
                                                {{ $endsAt->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                            @else
                                                Не указано
                                            @endif
                                        </p>
                                        <p class="card-text fw-semibold fs-5 mb-3">
                                            Ставка: {{ number_format($listing->price, 0, '.', ' ') }} {{ $listing->currency }}
                                        </p>
                                        <div class="mt-auto d-flex flex-wrap gap-2">
                                            <a href="{{ route('listings.show', $listing) }}" class="btn btn-brand-gradient">
                                                Подробнее
                                            </a>
                                            @if(auth()->id() === $listing->user_id)
                                                <a href="{{ route('auction-listings.edit', $listing) }}" class="btn btn-outline-secondary">
                                                    Редактировать
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="brand-surface text-center py-5 text-muted">
                        По заданным параметрам ничего не найдено. Попробуйте изменить фильтры.
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $listings->links() }}
            </div>
        </div>
    </section>
</x-app-layout>

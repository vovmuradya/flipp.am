<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Мои объявления') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Управляйте активными и черновыми объявлениями, редактируйте информацию или быстро создавайте новые карточки.') }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h3 class="h5 fw-semibold mb-0">{{ __('Всего объявлений: :count', ['count' => $listings->total()]) }}</h3>
                <a href="{{ route('listings.create') }}" class="btn btn-brand-gradient">
                    + {{ __('Создать объявление') }}
                </a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-lg-4">
                @forelse($listings as $listing)
                    <div class="col">
                        <div class="card shadow-sm border-0 rounded-3 h-100 overflow-hidden">
                            @php
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
                            <div class="ratio ratio-4x3 bg-light overflow-hidden rounded-top">
                                <a href="{{ route('listings.show', $listing) }}" class="d-flex w-100 h-100">
                                    <img src="{{ $previewImage }}"
                                         alt="{{ $listing->title }}"
                                         class="img-fluid w-100 h-100 object-fit-cover"
                                         loading="lazy"
                                         onerror="this.src='{{ $fallbackImage }}'">
                                </a>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <h5 class="card-title fs-6 fw-semibold mb-0 text-truncate" title="{{ $listing->title }}">
                                        {{ $listing->title }}
                                    </h5>
                                    <span class="badge rounded-pill {{ $listing->status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                        {{ $listing->status === 'active' ? __('Активно') : __('Черновик') }}
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-1">
                                    {{ $listing->region?->name ?? __('Регион не указан') }}
                                </p>
                                <p class="card-text fw-semibold mb-2">
                                    {{ number_format($listing->price, 0, '.', ' ') }} {{ $listing->currency }}
                                </p>
                                <p class="card-text text-muted small mt-auto mb-3">
                                    {{ __('Добавлено: :date', ['date' => $listing->created_at->format('d.m.Y')]) }}
                                </p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('listings.edit', $listing) }}" class="btn btn-sm btn-brand-gradient flex-grow-1">
                                        {{ __('Редактировать') }}
                                    </a>
                                    <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('{{ __('Удалить объявление?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                            {{ __('Удалить') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="brand-surface text-center py-5 text-muted">
                            {{ __('У вас пока нет объявлений. Нажмите «Создать объявление», чтобы добавить первое.') }}
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $listings->links() }}
            </div>
        </div>
    </section>
</x-app-layout>

<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Редактирование аукционного объявления') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Можно изменить цену и описание. Данные лота с аукциона остаются только для чтения.') }}
                </p>
            </div>

            <div class="brand-surface p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4" role="alert">
                        <p class="fw-semibold mb-2">{{ __('Обнаружены ошибки:') }}</p>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('auction-listings.update', $listing) }}" method="POST" class="d-flex flex-column gap-4">
                    @csrf
                    @method('PUT')

                    <div class="d-flex flex-column gap-1">
                        <label for="title" class="brand-form-label">{{ __('Заголовок') }}</label>
                        <input
                            type="text"
                            id="title"
                            value="{{ $listing->title }}"
                            readonly
                            class="brand-form-control"
                        >
                    </div>

                    @if($listing->vehicleDetail)
                        <div class="p-3 p-md-4 rounded-4" style="background: rgba(17,24,39,0.04); border: 1px solid rgba(17,24,39,0.08);">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <p class="profile-card__eyebrow mb-1">{{ __('Характеристики') }}</p>
                                    <h4 class="h6 fw-semibold mb-0">{{ __('Нередактируемые поля из аукциона') }}</h4>
                                </div>
                                @if($listing->vehicleDetail->source_auction_url)
                                    <a href="{{ $listing->vehicleDetail->source_auction_url }}" target="_blank" class="btn btn-brand-outline btn-sm">
                                        {{ __('Посмотреть на аукционе') }}
                                    </a>
                                @endif
                            </div>
                            <dl class="row mb-0 small">
                                <dt class="col-6 col-md-3 text-muted">{{ __('Марка') }}</dt>
                                <dd class="col-6 col-md-3">{{ $listing->vehicleDetail->make }}</dd>

                                <dt class="col-6 col-md-3 text-muted">{{ __('Модель') }}</dt>
                                <dd class="col-6 col-md-3">{{ $listing->vehicleDetail->model }}</dd>

                                <dt class="col-6 col-md-3 text-muted">{{ __('Год') }}</dt>
                                <dd class="col-6 col-md-3">{{ $listing->vehicleDetail->year }}</dd>

                                <dt class="col-6 col-md-3 text-muted">{{ __('Пробег') }}</dt>
                                <dd class="col-6 col-md-3">{{ number_format($listing->vehicleDetail->mileage) }} {{ __('км') }}</dd>
                            </dl>
                        </div>
                    @endif

                    <div class="d-flex flex-column gap-2">
                        <label for="price" class="brand-form-label">{{ __('Цена (AMD)') }}</label>
                        <input
                            type="number"
                            name="price"
                            id="price"
                            value="{{ old('price', $listing->price) }}"
                            required
                            class="brand-form-control"
                        >
                        @error('price')
                            <p class="profile-form__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <label for="description" class="brand-form-label">{{ __('Описание') }}</label>
                        <textarea
                            name="description"
                            id="description"
                            rows="5"
                            required
                            class="brand-form-control"
                            style="min-height: 140px;"
                        >{{ old('description', $listing->description) }}</textarea>
                        @error('description')
                            <p class="profile-form__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <label class="brand-form-label">{{ __('Фотографии') }}</label>
                        <div class="row g-3">
                            @forelse($listing->getMedia('images') as $media)
                                <div class="col-6 col-md-3">
                                    <div class="ratio ratio-4x3 rounded-3 overflow-hidden border">
                                        <img src="{{ $media->getUrl('thumb') }}" alt="{{ __('Фото') }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('Фотографии отсутствуют.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
                        <a href="{{ route('dashboard.my-auctions') }}" class="btn btn-brand-outline">
                            {{ __('Отмена') }}
                        </a>
                        <button type="submit" class="btn btn-brand-gradient">
                            {{ __('Сохранить изменения') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>

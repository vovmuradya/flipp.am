<div class="vehicle-filter-card {{ $fullWidth ?? false ? 'vehicle-filter-card--fullwidth' : 'brand-surface sticky-top' }}"
     @if(empty($fullWidth)) style="top: 90px; z-index: 1;" @endif>
    <form method="GET"
          action="{{ route('listings.index') }}"
          class="vstack gap-3"
          id="vehicle-filter-form">
        @if($mode === 'auction')
            <input type="hidden" name="only_auctions" value="1">
        @else
            <input type="hidden" name="only_regular" value="1">
        @endif

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Поиск</label>
            <input type="search"
                   name="q"
                   id="listing-search-input"
                   class="form-control"
                   placeholder="Например: Toyota Camry, шины R16"
                   value="{{ request('q') }}">
        </div>

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Марка</label>
            <div class="position-relative">
                <input type="text"
                       data-filter="brand"
                       name="brand"
                       class="form-control"
                       autocomplete="off"
                       placeholder="Введите и выберите"
                       value="{{ request('brand') }}">
                <div class="list-group shadow-sm position-absolute w-100"
                     data-suggestions="brand"
                     style="z-index: 30; display: none;"></div>
            </div>
        </div>

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Модель</label>
            <div class="position-relative">
                <input type="text"
                       data-filter="model"
                       name="model"
                       class="form-control"
                       autocomplete="off"
                       placeholder="Введите и выберите"
                       value="{{ request('model') }}">
                <div class="list-group shadow-sm position-absolute w-100"
                     data-suggestions="model"
                     style="z-index: 30; display: none;"></div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Цена от</label>
                <input type="number" name="price_from" class="form-control" value="{{ request('price_from') }}">
            </div>
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Цена до</label>
                <input type="number" name="price_to" class="form-control" value="{{ request('price_to') }}">
            </div>
        </div>

        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Год от</label>
                <input type="number" name="year_from" class="form-control" value="{{ request('year_from') }}">
            </div>
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Год до</label>
                <input type="number" name="year_to" class="form-control" value="{{ request('year_to') }}">
            </div>
        </div>

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Тип кузова</label>
            <select name="body_type" class="form-select">
                <option value="">Любой</option>
                @foreach($bodyOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('body_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Трансмиссия</label>
            <select name="transmission" class="form-select">
                <option value="">Любая</option>
                @foreach($transmissionOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('transmission') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label small text-uppercase text-muted fw-semibold">Топливо</label>
            <select name="fuel_type" class="form-select">
                <option value="">Любое</option>
                @foreach($fuelOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('fuel_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Двигатель от</label>
                <select name="engine_from" class="form-select">
                    <option value="">Любой</option>
                    @foreach($engineOptions as $option)
                        <option value="{{ $option['cc'] }}" @selected((string)request('engine_from') === (string)$option['cc'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small text-uppercase text-muted fw-semibold">Двигатель до</label>
                <select name="engine_to" class="form-select">
                    <option value="">Любой</option>
                    @foreach($engineOptions as $option)
                        <option value="{{ $option['cc'] }}" @selected((string)request('engine_to') === (string)$option['cc'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-grid gap-2 mt-2">
            <button type="submit" class="btn btn-brand-gradient">Применить</button>
            <a href="{{ route('listings.index', $resetParams) }}" class="btn btn-brand-outline">
                Сбросить
            </a>
        </div>
    </form>

    <div class="mt-4">
        <h5 class="h6 fw-semibold mb-3">Активные фильтры</h5>
        @if($activeFilters->isNotEmpty())
            <ul class="list-unstyled small text-muted mb-3">
                @foreach($activeFilters as $label => $value)
                    <li class="mb-1">
                        <span class="fw-semibold">{{ $label }}:</span>
                        <span>{{ $value }}</span>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('listings.index', $resetParams) }}" class="btn btn-sm btn-brand-outline w-100">
                Очистить всё
            </a>
        @else
            <p class="text-muted small mb-3">Вы ещё не применили фильтры. Используйте форму выше.</p>
        @endif
    </div>
</div>

@php
    use App\Support\VehicleAttributeOptions;

    $originValue = request('origin');
    if (!$originValue && isset($currentOrigin)) {
        $originValue = $currentOrigin;
    }
    if (!$originValue) {
        $originValue = request()->boolean('only_auctions') ? 'abroad' : (request()->boolean('only_regular') ? 'regular' : 'regular');
    }

    $currencyOptions = [
        'AMD' => '֏ AMD',
        'USD' => '$ USD',
    ];
    $currencyValue = strtoupper((string) request('currency', ''));
    if (!array_key_exists($currencyValue, $currencyOptions)) {
        $currencyValue = '';
    }

    $driveOptions = [
        'fwd' => __('Передний'),
        'rwd' => __('Задний'),
        'awd' => __('Полный'),
    ];

    $conditionOptions = [
        'undamaged' => __('Небитый'),
        'damaged' => __('Битый'),
    ];

    $colorOptions = \App\Support\VehicleAttributeOptions::colors();

    $fuelOptions = VehicleAttributeOptions::fuelTypes();
    $engineOptions = collect(range(10, 80))->map(function ($index) {
        $liters = $index / 10;
        $cc = (int) round($liters * 1000);

        return [
            'cc' => $cc,
            'label' => number_format($liters, 1, '.', '') . ' ' . __('л'),
        ];
    });
@endphp

@php
    $filterConfig = [
        'initialQuery' => request('q'),
        'initialOrigin' => $originValue ?? 'regular',
    ];
@endphp

<div class="brand-filter home-filter"
     x-data="filters(@js($filterConfig))">
    <form action="{{ route('search.index') }}" method="GET" x-ref="filterForm" class="home-filter__form">
        <div class="home-filter__origin-toggle">
            <input type="hidden" name="origin" :value="origin">
            <div class="origin-toggle">
                <button type="button"
                        class="origin-toggle__btn"
                        :class="{ 'is-active': origin === 'regular' }"
                        @click="setOrigin('regular')">
                    {{ __('В Армении') }}
                </button>
                <button type="button"
                        class="origin-toggle__btn"
                        :class="{ 'is-active': origin === 'abroad' }"
                        @click="setOrigin('abroad')">
                    {{ __('За рубежом') }}
                </button>
                <button type="button"
                        class="origin-toggle__btn"
                        :class="{ 'is-active': origin === 'transit' }"
                        @click="setOrigin('transit')">
                    {{ __('В пути') }}
                </button>
            </div>
        </div>

        <div class="home-filter__grid">
            <div class="home-filter__field home-filter__field--wide">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Поиск') }}</label>
                <input
                    type="text"
                    name="q"
                    class="form-control form-control-lg"
                    x-model="searchTerm"
                    x-ref="searchInput"
                    placeholder="{{ __('Например: Toyota Camry') }}"
                    inputmode="search">
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Марка') }}</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="brand"
                        class="form-control form-control-lg"
                        data-filter="brand"
                        autocomplete="off"
                        placeholder="{{ __('Введите марку') }}"
                        value="{{ request('brand') }}">
                    <div class="list-group shadow-sm position-absolute w-100"
                         data-suggestions="brand"
                         style="z-index: 30; display: none;"></div>
                </div>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Модель') }}</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="model"
                        class="form-control form-control-lg"
                        data-filter="model"
                        autocomplete="off"
                        placeholder="{{ __('Введите модель') }}"
                        value="{{ request('model') }}">
                    <div class="list-group shadow-sm position-absolute w-100"
                         data-suggestions="model"
                         style="z-index: 30; display: none;"></div>
                </div>
            </div>

            <div class="home-filter__field home-filter__field--region"
                 x-cloak
                 x-show="origin === 'regular'">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Регион') }}</label>
                <select name="region_id"
                        class="form-select form-select-lg"
                        x-ref="regionSelect"
                        :disabled="origin !== 'regular'">
                    <option value="">{{ __('Все регионы') }}</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>{{ $region->localized_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field home-filter__field--wide">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Цена') }}</label>
                <div class="filter-range-row filter-range-row--price">
                    <div class="filter-range-field">
                        <input type="number"
                               name="price_from"
                               class="form-control form-control-lg"
                               placeholder="{{ __('От') }}"
                               value="{{ request('price_from') }}">
                    </div>
                    <div class="filter-range-field">
                        <input type="number"
                               name="price_to"
                               class="form-control form-control-lg"
                               placeholder="{{ __('До') }}"
                               value="{{ request('price_to') }}">
                    </div>
                    <div class="filter-range-field filter-range-field--currency">
                        <select name="currency" class="form-select form-select-lg" aria-label="{{ __('Валюта') }}">
                            <option value="">{{ __('AMD / $') }}</option>
                            @foreach($currencyOptions as $code => $label)
                                <option value="{{ $code }}" @selected($currencyValue === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="home-filter__field home-filter__field--wide">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Год выпуска') }}</label>
                <div class="filter-range-row filter-range-row--double">
                    <div class="filter-range-field">
                        <input type="number"
                               name="year_from"
                               class="form-control form-control-lg"
                               placeholder="{{ __('От') }}"
                               value="{{ request('year_from') }}">
                    </div>
                    <div class="filter-range-field">
                        <input type="number"
                               name="year_to"
                               class="form-control form-control-lg"
                               placeholder="{{ __('До') }}"
                               value="{{ request('year_to') }}">
                    </div>
                </div>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Тип двигателя') }}</label>
                <select name="fuel_type" class="form-select form-select-lg">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($fuelOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('fuel_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Объём двигателя') }}</label>
                <div class="filter-range-row filter-range-row--double">
                    <div class="filter-range-field">
                        <select name="engine_from" class="form-select form-select-lg">
                            <option value="">{{ __('От') }}</option>
                            @foreach($engineOptions as $option)
                                <option value="{{ $option['cc'] }}" @selected((string)request('engine_from') === (string)$option['cc'])>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-range-field">
                        <select name="engine_to" class="form-select form-select-lg">
                            <option value="">{{ __('До') }}</option>
                            @foreach($engineOptions as $option)
                                <option value="{{ $option['cc'] }}" @selected((string)request('engine_to') === (string)$option['cc'])>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Привод') }}</label>
                <select name="drive_type" class="form-select form-select-lg">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($driveOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('drive_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Состояние') }}</label>
                <select name="condition" class="form-select form-select-lg">
                    <option value="">{{ __('Любое') }}</option>
                    @foreach($conditionOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('condition') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">{{ __('Цвет') }}</label>
                <select name="color" class="form-select form-select-lg">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($colorOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('color') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="home-filter__actions d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 mt-3">
            <button type="submit" class="btn btn-brand-gradient btn-sm fw-semibold px-4 py-2 action-btn">{{ __('Найти') }}</button>
            <a href="{{ route('search.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold px-4 py-2 action-btn">{{ __('Сбросить') }}</a>
        </div>
    </form>
</div>

<script>
    function filters(config) {
        return {
            searchTerm: config.initialQuery ?? '',
            origin: config.initialOrigin || 'regular',
            applyQuickFilter(value) {
                this.searchTerm = value;
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            },
            setOrigin(value) {
                this.origin = value;
                if (value !== 'regular' && this.$refs.regionSelect) {
                    this.$refs.regionSelect.value = '';
                }
            },
        }
    }
</script>

@include('listings.partials.brand-model-autocomplete-script')

@pushOnce('styles')
    <style>
        .home-filter__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 1rem;
        }

        .home-filter__field--wide {
            grid-column: 1 / -1;
        }

        .home-filter__field--region[x-cloak][x-show="false"] {
            display: none !important;
        }

        .origin-toggle {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .origin-toggle__btn {
            flex: 1 1 140px;
            border: 1px solid #ced4da;
            border-radius: 999px;
            padding: 0.55rem 1rem;
            background: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .origin-toggle__btn.is-active {
            background: #111827;
            color: #fff;
            border-color: #111827;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
        }

        .home-filter__field input,
        .home-filter__field select {
            min-height: 48px;
        }

        .filter-range-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-range-field {
            flex: 1 1 180px;
            min-width: 150px;
        }

        .filter-range-row--price .filter-range-field {
            flex: 1 1 200px;
        }

        .filter-range-row--price .filter-range-field:nth-child(1),
        .filter-range-row--price .filter-range-field:nth-child(2) {
            flex: 1 1 220px;
        }

        .filter-range-field--currency {
            flex: 0 0 120px;
            min-width: 120px;
        }

        .action-btn {
            min-width: 140px;
        }

        @media (min-width: 992px) {
            .filter-range-row--price {
                flex-wrap: nowrap;
            }

            .filter-range-row--double {
                flex-wrap: nowrap;
            }

            .filter-range-row--double .filter-range-field {
                flex: 0 0 calc(50% - 0.25rem);
            }
        }
    </style>
@endPushOnce

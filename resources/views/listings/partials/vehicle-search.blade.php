@php
    use App\Support\VehicleAttributeOptions;

    $mode = $mode ?? 'regular';
    $fullWidth = $fullWidth ?? false;

    $bodyOptions = VehicleAttributeOptions::bodyTypes();
    $transmissionOptions = VehicleAttributeOptions::transmissions();
    $fuelOptions = VehicleAttributeOptions::fuelTypes();

    $engineOptions = collect(range(1, 100))->map(function ($index) {
        $liters = $index / 10;
        $cc = (int) round($liters * 1000);

        return [
            'cc' => $cc,
            'label' => number_format($liters, 1, '.', '') . ' л',
        ];
    });

    $activeFilters = collect([
        'Поиск' => request('q'),
        'Марка' => request('brand'),
        'Модель' => request('model'),
        'Цена от' => request('price_from'),
        'Цена до' => request('price_to'),
        'Год от' => request('year_from'),
        'Год до' => request('year_to'),
        'Тип кузова' => request('body_type') ? ($bodyOptions[request('body_type')] ?? request('body_type')) : null,
        'Трансмиссия' => request('transmission') ? ($transmissionOptions[request('transmission')] ?? request('transmission')) : null,
        'Топливо' => request('fuel_type') ? ($fuelOptions[request('fuel_type')] ?? request('fuel_type')) : null,
        'Двигатель от' => request('engine_from') ? (request('engine_from') . ' см³') : null,
        'Двигатель до' => request('engine_to') ? (request('engine_to') . ' см³') : null,
    ])->filter();

    $resetParams = $mode === 'auction'
        ? ['only_auctions' => 1]
        : ['only_regular' => 1];
@endphp

@if($fullWidth)
    <div class="vehicle-fullwidth">
        <div class="vehicle-fullwidth__grid row g-4 g-xl-5 align-items-start">
            <aside class="vehicle-fullwidth__sidebar col-12 col-lg-3 order-1 order-lg-2">
                @include('listings.partials.vehicle-filter-form', [
                    'mode' => $mode,
                    'bodyOptions' => $bodyOptions,
                    'transmissionOptions' => $transmissionOptions,
                    'fuelOptions' => $fuelOptions,
                    'engineOptions' => $engineOptions,
                    'activeFilters' => $activeFilters,
                    'resetParams' => $resetParams,
                    'fullWidth' => true,
                ])
            </aside>
            <div class="vehicle-fullwidth__main col-12 col-lg-9 order-2 order-lg-1">
                <div class="listing-grid listing-grid--fullwidth">
                    @forelse ($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-center text-muted py-4">Объявлений пока нет. Попробуйте изменить фильтры.</p>
                    @endforelse
                </div>
                <div class="vehicle-fullwidth__pagination">
                    {{ $listings->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row g-4 align-items-start">
        <div class="col-12 col-lg-9 order-2 order-lg-1">
            <div class="brand-surface">
                <div class="listing-grid">
                    @forelse ($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-center text-muted py-4">Объявлений пока нет. Попробуйте изменить фильтры.</p>
                    @endforelse
                </div>
            </div>
            <div class="pt-3">
                {{ $listings->appends(request()->except('page'))->links() }}
            </div>
        </div>

        <div class="col-12 col-lg-3 order-1 order-lg-2">
            @include('listings.partials.vehicle-filter-form', [
                'mode' => $mode,
                'bodyOptions' => $bodyOptions,
                'transmissionOptions' => $transmissionOptions,
                'fuelOptions' => $fuelOptions,
                'engineOptions' => $engineOptions,
                'activeFilters' => $activeFilters,
                'resetParams' => $resetParams,
                'fullWidth' => false,
            ])
        </div>
    </div>
@endif

@include('listings.partials.brand-model-autocomplete-script')

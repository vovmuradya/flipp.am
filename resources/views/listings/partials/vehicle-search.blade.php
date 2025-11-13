@php
    use App\Support\VehicleAttributeOptions;

    $mode = $mode ?? null;
    $fullWidth = $fullWidth ?? false;
    $formAction = $formAction ?? null;
    $formMethod = $formMethod ?? 'GET';
    $resetUrl = $resetUrl ?? null;

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

    $resetParams = match ($mode) {
        'auction' => ['only_auctions' => 1],
        'regular' => ['only_regular' => 1],
        default => [],
    };
@endphp

@if($fullWidth)
    <div class="vehicle-fullwidth">
        <div class="vehicle-fullwidth__grid">
            <div class="vehicle-fullwidth__main vehicle-fullwidth__results" id="ajax-search-results" data-listings-container>
                <div class="listing-grid{{ $fullWidth ? ' listing-grid--compact' : '' }}">
                    @forelse ($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-center text-muted py-4">{{ __('Объявлений пока нет.') }}</p>
                    @endforelse
                </div>
                <div class="vehicle-fullwidth__pagination">
                    @if ($listings instanceof \Illuminate\Pagination\LengthAwarePaginator || $listings instanceof \Illuminate\Pagination\Paginator)
                        {{ $listings->appends(request()->except('page'))->links() }}
                    @endif
                </div>
            </div>
            <aside class="vehicle-fullwidth__sidebar">
                @include('listings.partials.vehicle-filter-form', [
                    'mode' => $mode,
                    'bodyOptions' => $bodyOptions,
                    'transmissionOptions' => $transmissionOptions,
                    'fuelOptions' => $fuelOptions,
                    'engineOptions' => $engineOptions,
                    'activeFilters' => $activeFilters,
                    'resetParams' => $resetParams,
                    'resetUrl' => $resetUrl,
                    'fullWidth' => false,
                    'formAction' => $formAction,
                    'formMethod' => $formMethod,
                ])
            </aside>
        </div>
    </div>
@else
    <div class="brand-surface" data-listings-container>
        <div class="listing-grid{{ $fullWidth ? ' listing-grid--compact' : '' }}">
            @forelse ($listings as $listing)
                <x-listing.card :listing="$listing" />
            @empty
                <p class="text-center text-muted py-4">{{ __('Объявлений пока нет.') }}</p>
            @endforelse
        </div>
    </div>
    <div class="pt-3" id="ajax-search-results">
        {{ $listings->appends(request()->except('page'))->links() }}
    </div>
@endif

@include('listings.partials.brand-model-autocomplete-script')

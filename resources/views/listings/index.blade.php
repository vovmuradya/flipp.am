<x-app-layout>
    @php
        $onlyRegular = $onlyRegular ?? false;
        $onlyAuctions = $onlyAuctions ?? false;
        $isFullWidth = $onlyRegular || $onlyAuctions;
    @endphp
    <section class="brand-section {{ $isFullWidth ? 'brand-section--fullwidth' : '' }}">
        @if(!$onlyRegular && !$onlyAuctions && $featuredListings->isNotEmpty())
            <div class="brand-slider brand-slider--fullwidth" data-slider="auction">
                <div class="brand-slider__header">
                    <div>
                        <h3 class="brand-slider__title">{{ __('Актуальные автомобили') }}</h3>
                        <p class="brand-slider__subtitle">{{ __('Смешанная подборка из аукционов и частных объявлений.') }}</p>
                    </div>
                </div>

                <div class="brand-slider__viewport" data-slider-viewport>
                    <div class="brand-slider__track" data-slider-track>
                        @foreach($featuredListings as $listing)
                            @php
                                $isAuction = $listing->isFromAuction();
                                $expiresAt = $isAuction ? optional($listing->vehicleDetail)->auction_ends_at : null;
                            @endphp
                            <div class="brand-slider__panel">
                                <x-listing.card
                                    :listing="$listing"
                                    :expires="$expiresAt"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="brand-slider__nav brand-slider__nav--floating">
                    <button type="button" class="brand-slider__nav-btn" data-slider-prev aria-label="{{ __('Предыдущие аукционные объявления') }}" disabled>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="brand-slider__nav-btn" data-slider-next aria-label="{{ __('Следующие аукционные объявления') }}">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(!$onlyRegular && !$onlyAuctions && ($dealers ?? collect())->isNotEmpty())
            <div class="brand-container mt-5">
                <div class="brand-slider__header mb-3">
                    <div>
                        <h3 class="brand-slider__title">{{ __('dealer.our_dealers') }}</h3>
                        <p class="brand-slider__subtitle">{{ __('dealer.become') }}</p>
                    </div>
                </div>
                <div class="dealer-logos-grid">
                    @foreach($dealers as $dealer)
                        <a href="{{ route('dealers.show', $dealer->slug) }}" class="dealer-logo-card">
                            @if($dealer->logo)
                                <img src="{{ $dealer->logo }}" alt="{{ $dealer->company_name }}" loading="lazy">
                            @else
                                <div class="dealer-logo-placeholder">{{ $dealer->company_name }}</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="brand-container {{ $isFullWidth ? 'brand-container--fluid' : '' }}">
            @if($onlyRegular)
                @include('listings.partials.vehicle-search', [
                    'listings' => $listings,
                    'brands' => $brands ?? collect(),
                    'mode' => 'regular',
                    'fullWidth' => true,
                ])
            @elseif($onlyAuctions)
                @include('listings.partials.vehicle-search', [
                    'listings' => $listings,
                    'brands' => $brands ?? collect(),
                    'mode' => 'auction',
                    'fullWidth' => true,
                ])
            @else
                <div class="brand-surface">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                        <h3 class="h5 mb-0">{{ __('Объявления') }}</h3>
                        <form method="GET" class="d-flex align-items-center gap-2">
                            @foreach(request()->except('sort') as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $val)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="sort" class="text-muted small mb-0">{{ __('Сортировка') }}</label>
                            <select name="sort" id="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>{{ __('Сначала новые') }}</option>
                                <option value="trust" {{ request('sort') === 'trust' ? 'selected' : '' }}>{{ __('По доверию (Seller Score)') }}</option>
                            </select>
                        </form>
                    </div>
                    <div class="listing-grid">
                    @forelse ($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-center text-muted py-4">{{ __('Объявлений пока нет.') }}</p>
                    @endforelse
                </div>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>

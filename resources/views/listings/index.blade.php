<x-app-layout>
    @php
        $onlyRegular = $onlyRegular ?? false;
        $onlyAuctions = $onlyAuctions ?? false;
        $isFullWidth = $onlyRegular || $onlyAuctions;
    @endphp
    <section class="brand-section {{ $isFullWidth ? 'brand-section--fullwidth' : '' }}">
        @if(!$onlyRegular && !$onlyAuctions && $featuredListings->isNotEmpty())
            <div class="brand-slider brand-slider--fullwidth mt-5" data-slider="auction">
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

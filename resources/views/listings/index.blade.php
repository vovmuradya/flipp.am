<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            @php $onlyRegular = $onlyRegular ?? false; @endphp
            <div class="brand-section__header">
                <h2 class="brand-section__title">Найдите идеальный автомобиль и комплектующие</h2>
                <p class="brand-section__subtitle">Тысячи актуальных объявлений по продаже авто, запчастей и шин. Выберите нужную категорию и начните поиск прямо сейчас.</p>
            </div>

            @include('listings._partials.filters')

            <div class="brand-surface">
                <div class="listing-grid">
                    @forelse ($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-center text-muted py-4">Объявлений пока нет.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $listings->links() }}
                </div>
            </div>

            @if(!$onlyRegular && isset($auctionListings) && $auctionListings->isNotEmpty())
                <div class="brand-section mt-5 pt-0">
                    <div class="brand-section__header">
                        <h3 class="brand-section__title">Аукционные автомобили</h3>
                        <p class="brand-section__subtitle">Актуальные лоты с Copart/IAAI. Успей сделать ставку до завершения торгов.</p>
                    </div>
                    <div class="brand-surface">
                        <div class="listing-grid">
                            @foreach($auctionListings as $listing)
                                <x-listing.card
                                    :listing="$listing"
                                    badge="Аукцион"
                                    :expires="optional($listing->vehicleDetail)->auction_ends_at"
                                />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>

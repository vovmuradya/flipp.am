<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header d-flex align-items-center gap-3">
                @if($dealer->logo)
                    <img src="{{ $dealer->logo }}" alt="{{ $dealer->company_name }}" style="max-height: 80px; max-width: 120px; object-fit: contain;">
                @endif
                <div>
                    <h1 class="brand-section__title mb-1">{{ $dealer->company_name }}</h1>
                    <p class="brand-section__subtitle mb-0">{{ $dealer->description }}</p>
                </div>
            </div>

            <div class="brand-surface mb-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="fw-semibold text-muted">{{ __('dealer.contacts') }}</div>
                        <div class="fs-6">{{ $dealer->phone ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="fw-semibold text-muted">{{ __('dealer.city') }}</div>
                        <div class="fs-6">{{ $dealer->city ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="fw-semibold text-muted">{{ __('dealer.listings_count') }}</div>
                        <div class="fs-6">{{ $dealer->listings_count ?? $dealer->listings()->count() }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="fw-semibold text-muted">{{ __('dealer.registered_at') }}</div>
                        <div class="fs-6">{{ optional($dealer->created_at)->format('d.m.Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="brand-surface">
                <h3 class="h5 mb-3">{{ __('Актуальные автомобили') }}</h3>
                <div class="listing-grid">
                    @forelse($dealer->listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @empty
                        <p class="text-muted">{{ __('Объявлений пока нет.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-app-layout>

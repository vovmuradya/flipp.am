<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('dealer.our_dealers') }}</h2>
                <p class="brand-section__subtitle">{{ __('dealer.become') }}</p>
            </div>
            <div class="row g-4">
                @forelse($dealers as $dealer)
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="{{ route('dealers.show', $dealer->slug) }}" class="dealer-logo-card h-100">
                            @if($dealer->logo)
                                <img src="{{ $dealer->logo }}" alt="{{ $dealer->company_name }}" class="img-fluid" loading="lazy">
                            @else
                                <div class="dealer-logo-placeholder">{{ $dealer->company_name }}</div>
                            @endif
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">
                        {{ __('Нет дилеров') }}
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $dealers->links() }}
            </div>
        </div>
    </section>
</x-app-layout>

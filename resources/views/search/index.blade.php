@php
    $searchMode = null;
    if (request()->boolean('only_auctions') || request('origin') === 'auction') {
        $searchMode = 'auction';
    } elseif (request()->boolean('only_regular') || request('origin') === 'regular') {
        $searchMode = 'regular';
    }
@endphp

<x-app-layout>
    <section class="brand-section brand-section--fullwidth">
        <div class="brand-container brand-container--fluid">
            @include('listings.partials.vehicle-search', [
                'listings' => $listings,
                'mode' => $searchMode,
                'fullWidth' => true,
                'formAction' => route('search.index'),
                'formMethod' => 'GET',
                'resetUrl' => route('search.index'),
            ])
        </div>
    </section>
</x-app-layout>

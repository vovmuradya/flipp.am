<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Последние объявления</h2>
                        <div class="flex gap-3">
                            @auth
                                @if(auth()->user()->isDealer() || auth()->user()->isAdmin())
                                    <a href="{{ route('listings.create-from-auction') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        Добавить с аукциона
                                    </a>
                                @endif
                            @endauth
                            <a href="{{ route('listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                                Подать объявление
                            </a>
                        </div>
                    </div>
                    {{-- Вставляем нашу форму фильтров --}}
                    @include('listings._partials.filters')
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse ($listings as $listing)
                            <x-listing.card :listing="$listing" />
                        @empty
                            <p class="col-span-full text-center text-gray-500 py-8">Объявлений пока нет.</p>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $listings->links() }}
                    </div>

                    @if(isset($auctionListings) && $auctionListings->isNotEmpty())
                        <div class="mt-12">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">Аукционные автомобили</h3>
                                <a href="{{ route('dashboard.my-auctions') }}" class="text-sm text-blue-600 hover:text-blue-800">Все аукционные объявления →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                @foreach($auctionListings as $listing)
                                    <x-listing.card
                                        :listing="$listing"
                                        badge="Аукцион"
                                        :expires="optional($listing->vehicleDetail)->auction_ends_at"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

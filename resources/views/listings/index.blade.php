<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Последние объявления</h2>
                        <a href="{{ route('listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Подать объявление
                        </a>
                    </div>
                    {{-- Вставляем нашу форму фильтров --}}
                    @include('listings._partials.filters')
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse ($listings as $listing)
                            <div class="border border-gray-200 rounded-lg shadow-sm overflow-hidden group">

                                {{-- НАЧАЛО: БЛОК С ИЗОБРАЖЕНИЕМ И СЛАЙДЕРОМ --}}
                                <div
                                    class="relative"
                                    x-data="{
            images: {{ json_encode($listing->getMedia('images')->map->getUrl('medium')) }},
            activeImage: 0
        }"
                                >
                                    <a href="{{ route('listings.show', $listing) }}">
                                        {{-- Основное изображение, которое будет меняться --}}
                                        <template x-if="images.length > 0">
                                            <img :src="images[activeImage]" alt="{{ $listing->title }}" class="w-full h-48 object-cover transition-opacity duration-300">
                                        </template>

                                        {{-- Заглушка, если изображений нет --}}
                                        <template x-if="images.length === 0">
                                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        </template>
                                    </a>

                                    {{-- Невидимый слой для отслеживания движения мыши --}}
                                    <div
                                        x-show="images.length > 1"
                                        @mousemove.throttle.100ms="
                const rect = $event.currentTarget.getBoundingClientRect();
                const x = $event.clientX - rect.left;
                const segmentWidth = rect.width / images.length;
                const segmentIndex = Math.floor(x / segmentWidth);
                activeImage = segmentIndex;
            "
                                        @mouseleave="activeImage = 0"
                                        class="absolute inset-0"
                                    ></div>

                                    {{-- Кнопка "В избранное" (ваш код) --}}
                                    @auth
                                        <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST" class="absolute top-2 right-2">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-full bg-white/70 backdrop-blur-sm hover:bg-white">
                                                @if(auth()->user()->favorites->contains($listing))
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endauth
                                </div>
                                {{-- КОНЕЦ: БЛОК С ИЗОБРАЖЕНИЕМ И СЛАЙДЕРОМ --}}

                                <a href="{{ route('listings.show', $listing) }}">
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg truncate">{{ $listing->title }}</h3>
                                        <p class="text-gray-600 mt-2 text-sm">{{ $listing->region?->name }}</p>
                                        <p class="text-xl font-semibold mt-4 text-indigo-600">${{ number_format($listing->price, 0, '.', ' ') }}</p>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <p class="col-span-full text-center text-gray-500 py-8">Объявлений пока нет.</p>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        {{ $listings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

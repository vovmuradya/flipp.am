<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- ЗАГОЛОВОК И СТАТИСТИКА --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold">
                                @if(request('q'))
                                    Результаты поиска: "{{ request('q') }}"
                                @else
                                    Все объявления
                                @endif
                            </h2>
                            <p class="text-gray-600 mt-1">Найдено: {{ $listings->total() }} объявлений</p>
                        </div>
                        <a href="{{ route('listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Подать объявление
                        </a>
                    </div>

                    {{-- ФОРМА ФИЛЬТРОВ --}}
                    <div class="bg-gray-100 p-6 rounded-lg mb-6">
                        <form action="{{ route('search.index') }}" method="GET">
                            {{-- ОСНОВНЫЕ ФИЛЬТРЫ --}}
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                {{-- Поисковый запрос --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                                    <input type="text" name="q" placeholder="Что вы ищете?"
                                           class="w-full rounded-md border-gray-300"
                                           value="{{ request('q') }}">
                                </div>

                                {{-- Категория --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
                                    <select name="category_id" class="w-full rounded-md border-gray-300">
                                        <option value="">Все категории</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                                {{ $category->current_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Регион --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Регион</label>
                                    <select name="region_id" class="w-full rounded-md border-gray-300">
                                        <option value="">Все регионы</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Кнопка поиска --}}
                                <div class="flex items-end">
                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                                        Найти
                                    </button>
                                </div>
                            </div>

                            {{-- ДОПОЛНИТЕЛЬНЫЕ ФИЛЬТРЫ --}}
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                                <div class="md:col-span-2"></div>

                                {{-- Цена от --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Цена от (USD)</label>
                                    <input type="number" name="price_from" placeholder="0"
                                           class="w-full rounded-md border-gray-300"
                                           value="{{ request('price_from') }}">
                                </div>

                                {{-- Цена до --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Цена до (USD)</label>
                                    <input type="number" name="price_to" placeholder="999999"
                                           class="w-full rounded-md border-gray-300"
                                           value="{{ request('price_to') }}">
                                </div>

                                {{-- Кнопка сброса --}}
                                <div class="flex items-end">
                                    <a href="{{ route('search.index') }}" class="w-full text-center bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">
                                        Сбросить
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- ПАНЕЛЬ СОРТИРОВКИ --}}
                    <div class="flex justify-between items-center mb-6 pb-4 border-b">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Сортировать:</span>
                            <div class="flex space-x-2">
                                @php
                                    $sortOptions = [
                                        'created_at' => 'По дате',
                                        'price' => 'По цене',
                                        'views_count' => 'По популярности',
                                        'title' => 'По названию'
                                    ];
                                @endphp

                                @foreach($sortOptions as $key => $label)
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => $key, 'sort_order' => request('sort_order', 'desc')]) }}"
                                       class="px-3 py-1 rounded-md text-sm {{ request('sort_by', 'created_at') == $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Переключатель направления сортировки --}}
                        <a href="{{ request()->fullUrlWithQuery(['sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}"
                           class="px-3 py-1 bg-gray-200 rounded-md text-sm hover:bg-gray-300">
                            @if(request('sort_order') == 'asc')
                                <span>↑ По возрастанию</span>
                            @else
                                <span>↓ По убыванию</span>
                            @endif
                        </a>
                    </div>

                    {{-- СПИСОК ОБЪЯВЛЕНИЙ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse ($listings as $listing)
                            <div class="border border-gray-200 rounded-lg shadow-sm overflow-hidden group hover:shadow-lg transition-shadow">
                                {{-- БЛОК С ИЗОБРАЖЕНИЕМ --}}
                                <div class="relative" x-data="{ images: {{ json_encode($listing->getMedia('images')->map->getUrl('medium')) }}, activeImage: 0 }">
                                    <a href="{{ route('listings.show', $listing) }}">
                                        <template x-if="images.length > 0">
                                            <img :src="images[activeImage]" alt="{{ $listing->title }}" class="w-full h-48 object-cover transition-opacity duration-300">
                                        </template>
                                        <template x-if="images.length === 0">
                                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                    </a>

                                    {{-- Слайдер изображений --}}
                                    <div x-show="images.length > 1"
                                         @mousemove.throttle.100ms="
                                            const rect = $event.currentTarget.getBoundingClientRect();
                                            const x = $event.clientX - rect.left;
                                            const segmentWidth = rect.width / images.length;
                                            activeImage = Math.floor(x / segmentWidth);
                                         "
                                         @mouseleave="activeImage = 0"
                                         class="absolute inset-0">
                                    </div>

                                    {{-- Избранное --}}
                                    @auth
                                        <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST" class="absolute top-2 right-2">
                                            @csrf
                                            <button type="submit" class="p-2 rounded-full bg-white/70 backdrop-blur-sm hover:bg-white">
                                                @if(auth()->user()->favorites->contains($listing))
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500">
                                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endauth
                                </div>

                                {{-- ИНФОРМАЦИЯ О ЛИСТИНГЕ --}}
                                <a href="{{ route('listings.show', $listing) }}">
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg truncate group-hover:text-blue-600 transition-colors">
                                            {{ $listing->title }}
                                        </h3>
                                        <p class="text-gray-600 mt-1 text-sm">
                                            {{ $listing->category?->current_name }} • {{ $listing->region?->name }}
                                        </p>
                                        <div class="flex justify-between items-center mt-4">
                                            <p class="text-xl font-semibold text-indigo-600">
                                                ${{ number_format($listing->price, 0, '.', ' ') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $listing->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Ничего не найдено</h3>
                                <p class="mt-1 text-sm text-gray-500">Попробуйте изменить параметры поиска</p>
                                <div class="mt-6">
                                    <a href="{{ route('search.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Сбросить фильтры
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- ПАГИНАЦИЯ --}}
                    <div class="mt-8">
                        {{ $listings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

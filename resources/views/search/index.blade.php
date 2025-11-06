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
                            @php
                                $filters = request()->input('filters', []);
                            @endphp
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

                            {{-- ФИЛЬТРЫ ДЛЯ АВТО --}}
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип объявления</label>
                                    <select name="listing_type" class="w-full rounded-md border-gray-300">
                                        <option value="">Любой</option>
                                        <option value="vehicle" @selected(request('listing_type') === 'vehicle')>Автомобили</option>
                                        <option value="parts" @selected(request('listing_type') === 'parts')>Запчасти</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Марка</label>
                                    <input type="text" name="filters[make]" class="w-full rounded-md border-gray-300" placeholder="Например, Toyota" value="{{ data_get($filters, 'make') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Модель</label>
                                    <input type="text" name="filters[model]" class="w-full rounded-md border-gray-300" placeholder="Camry" value="{{ data_get($filters, 'model') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Трансмиссия</label>
                                    @php($transmissions = ['automatic' => 'Автомат', 'manual' => 'Механика', 'cvt' => 'CVT', 'semi-automatic' => 'Робот'])
                                    <select name="filters[transmission]" class="w-full rounded-md border-gray-300">
                                        <option value="">Любая</option>
                                        @foreach($transmissions as $value => $label)
                                            <option value="{{ $value }}" @selected(data_get($filters, 'transmission') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Топливо</label>
                                    @php($fuels = ['gasoline' => 'Бензин', 'diesel' => 'Дизель', 'hybrid' => 'Гибрид', 'electric' => 'Электро', 'lpg' => 'ГАЗ'])
                                    <select name="filters[fuel_type]" class="w-full rounded-md border-gray-300">
                                        <option value="">Любое</option>
                                        @foreach($fuels as $value => $label)
                                            <option value="{{ $value }}" @selected(data_get($filters, 'fuel_type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Аукцион</label>
                                    <select name="filters[is_from_auction]" class="w-full rounded-md border-gray-300">
                                        <option value="">Все</option>
                                        <option value="1" @selected(data_get($filters, 'is_from_auction') === '1')>Только с аукциона</option>
                                        <option value="0" @selected(data_get($filters, 'is_from_auction') === '0')>Только частные</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Год от</label>
                                    <input type="number" name="filters[year][from]" class="w-full rounded-md border-gray-300" placeholder="2005" value="{{ data_get($filters, 'year.from') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Год до</label>
                                    <input type="number" name="filters[year][to]" class="w-full rounded-md border-gray-300" placeholder="2025" value="{{ data_get($filters, 'year.to') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Пробег от</label>
                                    <input type="number" name="filters[mileage][from]" class="w-full rounded-md border-gray-300" placeholder="0" value="{{ data_get($filters, 'mileage.from') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Пробег до</label>
                                    <input type="number" name="filters[mileage][to]" class="w-full rounded-md border-gray-300" placeholder="200000" value="{{ data_get($filters, 'mileage.to') }}">
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
                            <x-listing.card
                                :listing="$listing"
                                :badge="$listing->isFromAuction() ? 'Аукцион' : null"
                                :expires="$listing->auction_ends_at"
                                :showFavorite="true"
                            />
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

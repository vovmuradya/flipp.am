<div class="bg-gray-100 p-6 rounded-lg mb-6"
     x-data="filters({
        initialCategoryId: '{{ request('category_id') }}',
        initialFields: {{ json_encode($categories->find(request('category_id'))?->customFields ?? []) }}
     })">
    <form action="{{ route('search.index') }}" method="GET">
        {{-- ОСНОВНЫЕ ФИЛЬТРЫ --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Поисковый запрос --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                <input type="text" name="q" placeholder="Что вы ищете?" class="w-full rounded-md border-gray-300" value="{{ request('q') }}">
            </div>
            {{-- Категория --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
                <select name="category_id" class="w-full rounded-md border-gray-300" x-model="selectedCategory" @change="fetchFields">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Регион --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Регион</label>
                <select name="region_id" class="w-full rounded-md border-gray-300">
                    <option value="">Все регионы</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Кнопка поиска --}}
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Найти</button>
            </div>
        </div>

        {{-- ДОПОЛНИТЕЛЬНЫЕ ФИЛЬТРЫ (Цена и ДИНАМИЧЕСКИЕ ПОЛЯ) --}}
        <div class="mt-4 space-y-4">
            {{-- Цена --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2"></div>
                <div><input type="number" name="price_from" placeholder="Цена от" class="w-full rounded-md border-gray-300" value="{{ request('price_from') }}"></div>
                <div><input type="number" name="price_to" placeholder="Цена до" class="w-full rounded-md border-gray-300" value="{{ request('price_to') }}"></div>
                <div class="flex items-center"><a href="{{ route('search.index') }}" class="w-full text-center bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Сбросить</a></div>
            </div>

            {{-- Контейнер для кастомных полей --}}
            <div x-show="customFields.length > 0" class="grid grid-cols-1 md:grid-cols-5 gap-4 pt-4 border-t">
                <template x-for="field in customFields" :key="field.id">
                    {{-- Для числовых полей создаём два инпута "от" и "до" --}}
                    <template x-if="field.type === 'number'">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="field.name + ' от'"></label>
                            <input type="number" :name="'filters[' + field.key + '][from]'" class="w-full rounded-md border-gray-300">
                        </div>
                    </template>
                    <template x-if="field.type === 'number'">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="field.name + ' до'"></label>
                            <input type="number" :name="'filters[' + field.key + '][to]'" class="w-full rounded-md border-gray-300">
                        </div>
                    </template>

                    {{-- Для полей типа select создаём выпадающий список --}}
                    <template x-if="field.type === 'select'">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="field.name"></label>
                            <select :name="'filters[' + field.key + ']'" class="w-full rounded-md border-gray-300">
                                <option value="">Любой</option>
                                <template x-for="option in field.options">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </form>
</div>

<script>
    function filters(config) {
        return {
            selectedCategory: config.initialCategoryId,
            customFields: config.initialFields,
            fetchFields() {
                if (!this.selectedCategory) {
                    this.customFields = [];
                    return;
                }
                fetch(`/api/categories/${this.selectedCategory}/fields`)
                    .then(response => response.json())
                    .then(data => { this.customFields = data; });
            }
        }
    }
</script>

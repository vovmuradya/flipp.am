<div class="brand-filter"
     x-data="filters({
        initialCategoryId: '{{ request('category_id') }}',
        initialFields: {{ json_encode($categories->find(request('category_id'))?->customFields ?? []) }},
        initialQuery: @json(request('q'))
     })">
    <form action="{{ route('search.index') }}" method="GET" x-ref="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="mb-2">Поиск</label>
                <input
                    type="text"
                    name="q"
                    x-model="searchTerm"
                    x-ref="searchInput"
                    placeholder="Например: Toyota Camry, шины R16"
                    inputmode="search"
                >
            </div>

            <div>
                <label class="mb-2">Категория</label>
                <select name="category_id" x-model="selectedCategory" @change="fetchFields">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->localized_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2">Регион</label>
                <select name="region_id">
                    <option value="">Բոլոր մարզերը</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>{{ $region->localized_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="btn-brand-gradient btn-brand-full">Найти</button>
            </div>
        </div>

        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2"></div>
                <div><input type="number" name="price_from" placeholder="Цена от" value="{{ request('price_from') }}"></div>
                <div><input type="number" name="price_to" placeholder="Цена до" value="{{ request('price_to') }}"></div>
                <div class="flex items-center"><a href="{{ route('search.index') }}" class="btn-brand-outline btn-brand-full text-center">Сбросить</a></div>
            </div>

            <div x-show="customFields.length > 0" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Динамические поля -->
            </div>
        </div>

        <div class="quick-filters mt-5">
            <span class="quick-filters__label">Популярные запросы:</span>
            <div class="quick-filters__chips">
                <button type="button" class="quick-filter__chip" @click="applyQuickFilter('Легковые автомобили')">Легковые</button>
                <button type="button" class="quick-filter__chip" @click="applyQuickFilter('Запчасти')">Запчасти</button>
                <button type="button" class="quick-filter__chip" @click="applyQuickFilter('Шины R17')">Шины</button>
                <button type="button" class="quick-filter__chip" @click="applyQuickFilter('Toyota')">Toyota</button>
                <button type="button" class="quick-filter__chip" @click="applyQuickFilter('Mercedes')">Mercedes</button>
            </div>
        </div>
    </form>
</div>

<script>
    function filters(config) {
        return {
            selectedCategory: config.initialCategoryId,
            customFields: config.initialFields,
            searchTerm: config.initialQuery ?? '',
            fetchFields() {
                if (!this.selectedCategory) {
                    this.customFields = [];
                    return;
                }
                fetch(`/api/categories/${this.selectedCategory}/fields`)
                    .then(response => response.json())
                    .then(data => { this.customFields = data; });
            },
            applyQuickFilter(value) {
                this.searchTerm = value;
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            }
        }
    }
</script>

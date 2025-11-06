<div class="brand-filter home-filter"
     x-data="filters({
        initialCategoryId: '{{ request('category_id') }}',
        initialFields: {{ json_encode($categories->find(request('category_id'))?->customFields ?? []) }},
        initialQuery: @json(request('q'))
     })">
    <form action="{{ route('search.index') }}" method="GET" x-ref="filterForm" class="home-filter__form">
        <div class="home-filter__fields">
            <div class="home-filter__field home-filter__field--search">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Поиск</label>
                <input
                    type="text"
                    name="q"
                    class="form-control form-control-lg"
                    x-model="searchTerm"
                    x-ref="searchInput"
                    placeholder="Например: Toyota Camry, шины R16"
                    inputmode="search">
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Категория</label>
                <select name="category_id"
                        class="form-select form-select-lg"
                        x-model="selectedCategory"
                        @change="fetchFields">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->localized_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Регион</label>
                <select name="region_id" class="form-select form-select-lg">
                    <option value="">Բոլոր մարզերը</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>{{ $region->localized_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Цена от</label>
                <input type="number"
                       name="price_from"
                       class="form-control form-control-lg"
                       placeholder="AMD"
                       value="{{ request('price_from') }}">
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Цена до</label>
                <input type="number"
                       name="price_to"
                       class="form-control form-control-lg"
                       placeholder="AMD"
                       value="{{ request('price_to') }}">
            </div>
        </div>

        <div x-show="customFields.length > 0" class="home-filter__dynamic mt-3">
            <div class="row g-3">
                <!-- Динамические поля -->
            </div>
        </div>

        <div class="home-filter__actions">
            <button type="submit" class="btn btn-brand-gradient btn-lg w-100">Найти</button>
            <a href="{{ route('search.index') }}" class="btn btn-outline-secondary btn-lg w-100 mt-2 mt-lg-0">Сбросить</a>
        </div>

        <div class="quick-filters mt-4">
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

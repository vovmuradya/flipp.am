<div class="brand-filter home-filter"
     x-data="filters({
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
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Марка</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="brand"
                        class="form-control form-control-lg"
                        data-filter="brand"
                        autocomplete="off"
                        placeholder="Введите марку"
                        value="{{ request('brand') }}">
                    <div class="list-group shadow-sm position-absolute w-100"
                         data-suggestions="brand"
                         style="z-index: 30; display: none;"></div>
                </div>
            </div>

            <div class="home-filter__field">
                <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Модель</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="model"
                        class="form-control form-control-lg"
                        data-filter="model"
                        autocomplete="off"
                        placeholder="Введите модель"
                        value="{{ request('model') }}">
                    <div class="list-group shadow-sm position-absolute w-100"
                         data-suggestions="model"
                         style="z-index: 30; display: none;"></div>
                </div>
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

        <div class="home-filter__actions">
            <button type="submit" class="btn btn-brand-gradient btn-lg w-100 w-md-auto">Найти</button>
            <a href="{{ route('search.index') }}" class="btn btn-outline-secondary btn-lg w-100 w-md-auto mt-2 mt-md-0">Сбросить</a>
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
            searchTerm: config.initialQuery ?? '',
            applyQuickFilter(value) {
                this.searchTerm = value;
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            }
        }
    }
</script>

@include('listings.partials.brand-model-autocomplete-script')

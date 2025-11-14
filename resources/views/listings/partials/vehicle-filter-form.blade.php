@php
    use App\Support\VehicleAttributeOptions;
    $colorOptions = $colorOptions ?? VehicleAttributeOptions::colors();
    $formAction = $formAction ?? route('listings.index');
    $formMethod = $formMethod ?? 'GET';
    $filterMode = $mode ?? null;
    $resetParams = $resetParams ?? [];
    $resetUrl = $resetUrl ?? route('listings.index', $resetParams);
    $collapsibleId = $collapsibleId ?? 'vehicle-filter-collapsible-' . uniqid();
@endphp

<div class="vehicle-filter-card {{ $fullWidth ?? false ? 'vehicle-filter-card--fullwidth' : 'brand-surface sticky-top' }}"
     data-filter-card
     @if(empty($fullWidth)) style="top: 90px; z-index: 1;" @endif>
    <form method="{{ $formMethod }}"
          action="{{ $formAction }}"
          class="vehicle-filter-form"
          id="vehicle-filter-form">
        @if($filterMode === 'auction')
            <input type="hidden" name="only_auctions" value="1">
        @elseif($filterMode === 'regular')
            <input type="hidden" name="only_regular" value="1">
        @endif

        {{-- Поиск --}}
        <div class="vehicle-filter-row" data-filter-mobile-toggle aria-controls="{{ $collapsibleId }}">
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Поиск') }}</label>
            <input type="search"
                   name="q"
                   id="listing-search-input"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                   placeholder="{{ __('Например: Toyota Camry, шины R16') }}"
                   value="{{ request('q') }}">
        </div>

        <div id="{{ $collapsibleId }}" data-filter-collapsible>
        {{-- Марка --}}
        <div class="vehicle-filter-row">
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Марка') }}</label>
            <div class="relative mt-1">
                <input type="text"
                       data-filter="brand"
                       name="brand"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       autocomplete="off"
                       placeholder="{{ __('Введите и выберите') }}"
                       value="{{ request('brand') }}">
                <div class="list-group shadow-sm absolute left-0 right-0 mt-1 z-30 hidden"
                     data-suggestions="brand"></div>
            </div>
        </div>

        {{-- Модель --}}
        <div class="vehicle-filter-row vehicle-filter-row--model">
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Модель') }}</label>
            <div class="relative mt-1">
                <input type="text"
                       data-filter="model"
                       name="model"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       autocomplete="off"
                       placeholder="{{ __('Введите и выберите') }}"
                       value="{{ request('model') }}">
                <div class="list-group shadow-sm absolute left-0 right-0 mt-1 z-30 hidden"
                     data-suggestions="model"></div>
            </div>
        </div>

        {{-- Цена: от / до / валюта --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-3">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цена от') }}</label>
                <input type="number" name="price_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('price_from') }}">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цена до') }}</label>
                <input type="number" name="price_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('price_to') }}">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Валюта') }}</label>
                <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('AMD / $') }}</option>
                    <option value="AMD" @selected(request('currency') === 'AMD')>֏ AMD</option>
                    <option value="USD" @selected(request('currency') === 'USD')>$ USD</option>
                </select>
            </div>
        </div>

        {{-- Год: от / до --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Год от') }}</label>
                <input type="number" name="year_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('year_from') }}">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Год до') }}</label>
                <input type="number" name="year_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('year_to') }}">
            </div>
        </div>

        {{-- Тип кузова / Трансмиссия --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Тип кузова') }}</label>
                <select name="body_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($bodyOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('body_type') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Трансмиссия') }}</label>
                <select name="transmission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любая') }}</option>
                    @foreach($transmissionOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('transmission') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Топливо / Привод --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Топливо') }}</label>
                <select name="fuel_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любое') }}</option>
                    @foreach($fuelOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('fuel_type') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Привод') }}</label>
                <select name="drive_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любой') }}</option>
                    <option value="fwd" @selected(request('drive_type') === 'fwd')>{{ __('Передний') }}</option>
                    <option value="rwd" @selected(request('drive_type') === 'rwd')>{{ __('Задний') }}</option>
                    <option value="awd" @selected(request('drive_type') === 'awd')>{{ __('Полный') }}</option>
                </select>
            </div>
        </div>

        {{-- Двигатель --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Двигатель от') }}</label>
                <select name="engine_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($engineOptions as $option)
                        <option value="{{ $option['cc'] }}" @selected((string)request('engine_from') === (string)$option['cc'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Двигатель до') }}</label>
                <select name="engine_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($engineOptions as $option)
                        <option value="{{ $option['cc'] }}" @selected((string)request('engine_to') === (string)$option['cc'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Цвет / Состояние --}}
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цвет') }}</label>
                <select name="color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любой') }}</option>
                    @foreach($colorOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('color') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Состояние') }}</label>
                <select name="condition" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">{{ __('Любое') }}</option>
                    <option value="undamaged" @selected(request('condition') === 'undamaged')>{{ __('Небитый') }}</option>
                    <option value="damaged" @selected(request('condition') === 'damaged')>{{ __('Битый') }}</option>
                </select>
            </div>
        </div>

        {{-- Кнопки --}}
        <div class="vehicle-filter-row vehicle-filter-row--actions">
            <button type="submit" class="btn btn-brand-gradient flex-1">{{ __('Применить') }}</button>
            <a href="{{ $resetUrl }}" class="btn btn-brand-outline flex-1 text-center">{{ __('Сбросить') }}</a>
        </div>
        <button type="button" class="btn btn-link p-0 text-decoration-underline" data-filter-close-mobile>{{ __('Скрыть фильтры') }}</button>
        </div>

    </form>

    <script>
        const vehicleFilterMessages = {
            searching: @json(__('Поиск...')),
            empty: @json(__('Ничего не найдено')),
            error: @json(__('Ошибка при загрузке результатов.')),
        };

        (function () {
            const form = document.getElementById('vehicle-filter-form');
            if (!form) return;

            const card = form.closest('[data-filter-card]');
            const mobileQuery = window.matchMedia('(max-width: 767.98px)');
            const closeButtons = form.querySelectorAll('[data-filter-close-mobile]');
            const searchInput = form.querySelector('#listing-search-input');

            const setMobileState = () => {
                if (!card) return;
                if (mobileQuery.matches) {
                    card.dataset.mobileExpanded = card.dataset.mobileExpanded === 'true' ? 'true' : 'false';
                } else {
                    card.removeAttribute('data-mobile-expanded');
                }
            };

            const openMobileFilters = () => {
                if (!card || !mobileQuery.matches) return;
                card.dataset.mobileExpanded = 'true';
            };

            const closeMobileFilters = () => {
                if (!card || !mobileQuery.matches) return;
                card.dataset.mobileExpanded = 'false';
            };

            setMobileState();
            mobileQuery.addEventListener('change', () => {
                setMobileState();
            });

            if (searchInput) {
                searchInput.addEventListener('focus', openMobileFilters);
            }

            closeButtons.forEach((btn) => btn.addEventListener('click', (event) => {
                event.preventDefault();
                closeMobileFilters();
            }));

            document.addEventListener('click', (event) => {
                if (!card || !mobileQuery.matches) {
                    return;
                }
                if (card.dataset.mobileExpanded !== 'true') {
                    return;
                }
                if (card.contains(event.target)) {
                    return;
                }
                closeMobileFilters();
            });

            const resultSelectors = [
                '#listings-grid', '.listings-grid', '.listings', '.results-list', '.listings-list', '[data-listings-container]'
            ];

            function findResultsHtml(doc) {
                for (const s of resultSelectors) {
                    const el = doc.querySelector(s);
                    if (el) return el.innerHTML;
                }
                const main = doc.querySelector('main') || doc.querySelector('section') || doc.body;
                return main ? main.innerHTML : null;
            }

            async function submitAjax(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : null;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = vehicleFilterMessages.searching;
                }

                const params = new URLSearchParams(new FormData(form));
                const url = form.action + (form.action.includes('?') ? '&' : '?') + params.toString();

                try {
                    const resp = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html, application/xhtml+xml'
                        },
                        credentials: 'same-origin'
                    });

                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    const html = await resp.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const content = findResultsHtml(doc);
                    const target = document.getElementById('ajax-search-results');
                    if (!target) return;

                    if (content) {
                        // Вставляем найденную часть страницы (список карточек) в контейнер
                        target.innerHTML = content;

                        // Обновляем URL в адресной строке без перезагрузки
                        try {
                            const newUrl = url;
                            window.history.pushState({}, '', newUrl);
                        } catch (err) {
                            // ignore
                        }

                        // Re-initialize any JS needed for new content (Alpine, sliders etc.)
                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            try { window.Alpine.initTree(target); } catch (e) { /* ignore */ }
                        }
                    } else {
                        target.innerHTML = `<div class="text-sm text-gray-600">${vehicleFilterMessages.empty}</div>`;
                    }

                } catch (err) {
                    console.error('Ajax search error:', err);
                    const target = document.getElementById('ajax-search-results');
                    if (target) target.innerHTML = `<div class="text-sm text-red-600">${vehicleFilterMessages.error}</div>`;
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (originalText) submitBtn.innerHTML = originalText;
                    }
                }
            }

            // Отправка формы через ajax
            form.addEventListener('submit', submitAjax);

            // Поддержка back/forward - восстанавливаем контент при навигации
            window.addEventListener('popstate', async function () {
                const params = new URLSearchParams(window.location.search);
                if ([...params].length === 0) return; // ничего не делать

                try {
                    const resp = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!resp.ok) return;
                    const html = await resp.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const content = findResultsHtml(doc);
                    const target = document.getElementById('ajax-search-results');
                    if (target && content) target.innerHTML = content;
                } catch (err) {
                    // ignore
                }
            });

        })();
    </script>
</div>

@php
    use App\Support\VehicleAttributeOptions;
    $colorOptions = $colorOptions ?? VehicleAttributeOptions::colors();
@endphp

<div class="vehicle-filter-card {{ $fullWidth ?? false ? 'vehicle-filter-card--fullwidth' : 'brand-surface sticky-top' }}"
     @if(empty($fullWidth)) style="top: 90px; z-index: 1;" @endif>
    <form method="GET"
          action="{{ route('listings.index') }}"
          class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end"
          id="vehicle-filter-form">
        @if($mode === 'auction')
            <input type="hidden" name="only_auctions" value="1">
        @else
            <input type="hidden" name="only_regular" value="1">
        @endif

        {{-- Поиск (поле занимает всю ширину) --}}
        <div class="sm:col-span-2 md:col-span-4">
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Поиск') }}</label>
            <input type="search"
                   name="q"
                   id="listing-search-input"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                   placeholder="{{ __('Например: Toyota Camry, шины R16') }}"
                   value="{{ request('q') }}">
        </div>

        {{-- Марка и Модель --}}
        <div>
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

        <div>
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

        {{-- Цена: from / to / currency --}}
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цена от') }}</label>
            <input type="number" name="price_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('price_from') }}">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цена до') }}</label>
            <input type="number" name="price_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('price_to') }}">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Валюта') }}</label>
            <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('AMD / $') }}</option>
                <option value="AMD" @selected(request('currency') === 'AMD')>֏ AMD</option>
                <option value="USD" @selected(request('currency') === 'USD')>$ USD</option>
            </select>
        </div>

        {{-- Год: from / to --}}
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Год от') }}</label>
            <input type="number" name="year_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('year_from') }}">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Год до') }}</label>
            <input type="number" name="year_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="{{ request('year_to') }}">
        </div>

        {{-- Тип кузова / Трансмиссия --}}
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Тип кузова') }}</label>
            <select name="body_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любой') }}</option>
                @foreach($bodyOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('body_type') === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Трансмиссия') }}</label>
            <select name="transmission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любая') }}</option>
                @foreach($transmissionOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('transmission') === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Топливо --}}
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Топливо') }}</label>
            <select name="fuel_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любое') }}</option>
                @foreach($fuelOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('fuel_type') === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Двигатель from/to --}}
        <div class="md:col-span-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
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
                <div>
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
        </div>

        {{-- Привод / Состояние / Цвет --}}
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Привод') }}</label>
            <select name="drive_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любой') }}</option>
                <option value="fwd">{{ __('Передний') }}</option>
                <option value="rwd">{{ __('Задний') }}</option>
                <option value="awd">{{ __('Полный') }}</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Состояние') }}</label>
            <select name="condition" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любое') }}</option>
                <option value="undamaged">{{ __('Небитый') }}</option>
                <option value="damaged">{{ __('Битый') }}</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-gray-500">{{ __('Цвет') }}</label>
            <select name="color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                <option value="">{{ __('Любой') }}</option>
                @foreach($colorOptions as $key => $label)
                    <option value="{{ $key }}" @selected(request('color') === $key)>{{ __($label) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Кнопки (на всю ширину формы) --}}
        <div class="sm:col-span-2 md:col-span-4 flex gap-2 mt-2">
            <button type="submit" class="btn btn-brand-gradient flex-1">{{ __('Применить') }}</button>
            <a href="{{ route('listings.index', $resetParams) }}" class="btn btn-brand-outline flex-1 text-center">{{ __('Сбросить') }}</a>
        </div>

    </form>

    <div id="ajax-search-results" class="mt-4"></div>

    <script>
        const vehicleFilterMessages = {
            searching: @json(__('Поиск...')),
            empty: @json(__('Ничего не найдено')),
            error: @json(__('Ошибка при загрузке результатов.')),
        };

        (function () {
            const form = document.getElementById('vehicle-filter-form');
            if (!form) return;

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

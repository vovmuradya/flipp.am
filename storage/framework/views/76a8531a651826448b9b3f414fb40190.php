<?php
    use App\Support\VehicleAttributeOptions;
    $colorOptions = $colorOptions ?? VehicleAttributeOptions::colors();
    $formAction = $formAction ?? route('listings.index');
    $formMethod = $formMethod ?? 'GET';
    $filterMode = $mode ?? null;
    $resetParams = $resetParams ?? [];
    $resetUrl = $resetUrl ?? route('listings.index', $resetParams);
    $collapsibleId = $collapsibleId ?? 'vehicle-filter-collapsible-' . uniqid();
?>

<div class="vehicle-filter-card <?php echo e($fullWidth ?? false ? 'vehicle-filter-card--fullwidth' : 'brand-surface sticky-top'); ?>"
     data-filter-card
     <?php if(empty($fullWidth)): ?> style="top: 90px; z-index: 1;" <?php endif; ?>>
    <form method="<?php echo e($formMethod); ?>"
          action="<?php echo e($formAction); ?>"
          class="vehicle-filter-form"
          id="vehicle-filter-form">
        <?php if($filterMode === 'auction'): ?>
            <input type="hidden" name="only_auctions" value="1">
        <?php elseif($filterMode === 'regular'): ?>
            <input type="hidden" name="only_regular" value="1">
        <?php endif; ?>

        
        <div class="vehicle-filter-row" data-filter-mobile-toggle aria-controls="<?php echo e($collapsibleId); ?>">
            <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Поиск')); ?></label>
            <input type="search"
                   name="q"
                   id="listing-search-input"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                   placeholder="<?php echo e(__('Например: Toyota Camry, шины R16')); ?>"
                   value="<?php echo e(request('q')); ?>">
        </div>

        <div id="<?php echo e($collapsibleId); ?>" data-filter-collapsible>
        
        <div class="vehicle-filter-row">
            <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Марка')); ?></label>
            <div class="relative mt-1">
                <input type="text"
                       data-filter="brand"
                       name="brand"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       autocomplete="off"
                       placeholder="<?php echo e(__('Введите и выберите')); ?>"
                       value="<?php echo e(request('brand')); ?>">
                <div class="list-group shadow-sm absolute left-0 right-0 mt-1 z-30 hidden"
                     data-suggestions="brand"></div>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--model">
            <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Модель')); ?></label>
            <div class="relative mt-1">
                <input type="text"
                       data-filter="model"
                       name="model"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       autocomplete="off"
                       placeholder="<?php echo e(__('Введите и выберите')); ?>"
                       value="<?php echo e(request('model')); ?>">
                <div class="list-group shadow-sm absolute left-0 right-0 mt-1 z-30 hidden"
                     data-suggestions="model"></div>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-3">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Цена от')); ?></label>
                <input type="number" name="price_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="<?php echo e(request('price_from')); ?>">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Цена до')); ?></label>
                <input type="number" name="price_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="<?php echo e(request('price_to')); ?>">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Валюта')); ?></label>
                <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('AMD / $')); ?></option>
                    <option value="AMD" <?php if(request('currency') === 'AMD'): echo 'selected'; endif; ?>>֏ AMD</option>
                    <option value="USD" <?php if(request('currency') === 'USD'): echo 'selected'; endif; ?>>$ USD</option>
                </select>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Год от')); ?></label>
                <input type="number" name="year_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="<?php echo e(request('year_from')); ?>">
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Год до')); ?></label>
                <input type="number" name="year_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" value="<?php echo e(request('year_to')); ?>">
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Тип кузова')); ?></label>
                <select name="body_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любой')); ?></option>
                    <?php $__currentLoopData = $bodyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('body_type') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Трансмиссия')); ?></label>
                <select name="transmission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любая')); ?></option>
                    <?php $__currentLoopData = $transmissionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('transmission') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Топливо')); ?></label>
                <select name="fuel_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любое')); ?></option>
                    <?php $__currentLoopData = $fuelOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('fuel_type') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Привод')); ?></label>
                <select name="drive_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любой')); ?></option>
                    <option value="fwd" <?php if(request('drive_type') === 'fwd'): echo 'selected'; endif; ?>><?php echo e(__('Передний')); ?></option>
                    <option value="rwd" <?php if(request('drive_type') === 'rwd'): echo 'selected'; endif; ?>><?php echo e(__('Задний')); ?></option>
                    <option value="awd" <?php if(request('drive_type') === 'awd'): echo 'selected'; endif; ?>><?php echo e(__('Полный')); ?></option>
                </select>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Двигатель от')); ?></label>
                <select name="engine_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любой')); ?></option>
                    <?php $__currentLoopData = $engineOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['cc']); ?>" <?php if((string)request('engine_from') === (string)$option['cc']): echo 'selected'; endif; ?>>
                            <?php echo e($option['label']); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Двигатель до')); ?></label>
                <select name="engine_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любой')); ?></option>
                    <?php $__currentLoopData = $engineOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['cc']); ?>" <?php if((string)request('engine_to') === (string)$option['cc']): echo 'selected'; endif; ?>>
                            <?php echo e($option['label']); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--split-2">
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Цвет')); ?></label>
                <select name="color" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любой')); ?></option>
                    <?php $__currentLoopData = $colorOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(request('color') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="vehicle-filter-field">
                <label class="text-xs font-semibold uppercase text-gray-500"><?php echo e(__('Состояние')); ?></label>
                <select name="condition" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value=""><?php echo e(__('Любое')); ?></option>
                    <option value="undamaged" <?php if(request('condition') === 'undamaged'): echo 'selected'; endif; ?>><?php echo e(__('Небитый')); ?></option>
                    <option value="damaged" <?php if(request('condition') === 'damaged'): echo 'selected'; endif; ?>><?php echo e(__('Битый')); ?></option>
                </select>
            </div>
        </div>

        
        <div class="vehicle-filter-row vehicle-filter-row--actions">
            <button type="submit" class="btn btn-brand-gradient flex-1"><?php echo e(__('Применить')); ?></button>
            <a href="<?php echo e($resetUrl); ?>" class="btn btn-brand-outline flex-1 text-center"><?php echo e(__('Сбросить')); ?></a>
        </div>
        <button type="button" class="btn btn-link p-0 text-decoration-underline" data-filter-close-mobile><?php echo e(__('Скрыть фильтры')); ?></button>
        </div>

    </form>

    <script>
        const vehicleFilterMessages = {
            searching: <?php echo json_encode(__('Поиск...'), 15, 512) ?>,
            empty: <?php echo json_encode(__('Ничего не найдено'), 15, 512) ?>,
            error: <?php echo json_encode(__('Ошибка при загрузке результатов.'), 15, 512) ?>,
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
<?php /**PATH /var/www/html/resources/views/listings/partials/vehicle-filter-form.blade.php ENDPATH**/ ?>
<?php
    use App\Support\VehicleAttributeOptions;

    $mode = $mode ?? null;
    $fullWidth = $fullWidth ?? false;
    $formAction = $formAction ?? null;
    $formMethod = $formMethod ?? 'GET';
    $resetUrl = $resetUrl ?? null;

    $bodyOptions = VehicleAttributeOptions::bodyTypes();
    $transmissionOptions = VehicleAttributeOptions::transmissions();
    $fuelOptions = VehicleAttributeOptions::fuelTypes();

    $engineOptions = collect(range(1, 100))->map(function ($index) {
        $liters = $index / 10;
        $cc = (int) round($liters * 1000);

        return [
            'cc' => $cc,
            'label' => number_format($liters, 1, '.', '') . ' л',
        ];
    });

    $activeFilters = collect([
        'Поиск' => request('q'),
        'Марка' => request('brand'),
        'Модель' => request('model'),
        'Цена от' => request('price_from'),
        'Цена до' => request('price_to'),
        'Год от' => request('year_from'),
        'Год до' => request('year_to'),
        'Тип кузова' => request('body_type') ? ($bodyOptions[request('body_type')] ?? request('body_type')) : null,
        'Трансмиссия' => request('transmission') ? ($transmissionOptions[request('transmission')] ?? request('transmission')) : null,
        'Топливо' => request('fuel_type') ? ($fuelOptions[request('fuel_type')] ?? request('fuel_type')) : null,
        'Двигатель от' => request('engine_from') ? (request('engine_from') . ' см³') : null,
        'Двигатель до' => request('engine_to') ? (request('engine_to') . ' см³') : null,
    ])->filter();

    $resetParams = match ($mode) {
        'auction' => ['only_auctions' => 1],
        'regular' => ['only_regular' => 1],
        default => [],
    };
?>

<?php if($fullWidth): ?>
    <div class="vehicle-fullwidth">
        <div class="vehicle-fullwidth__grid">
            <div class="vehicle-fullwidth__main vehicle-fullwidth__results" id="ajax-search-results" data-listings-container>
                <div class="listing-grid<?php echo e($fullWidth ? ' listing-grid--compact' : ''); ?>">
                    <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if (isset($component)) { $__componentOriginal29dcf2c56c04a0abdc193077dccc46e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.listing.card','data' => ['listing' => $listing]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('listing.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8)): ?>
<?php $attributes = $__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8; ?>
<?php unset($__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal29dcf2c56c04a0abdc193077dccc46e8)): ?>
<?php $component = $__componentOriginal29dcf2c56c04a0abdc193077dccc46e8; ?>
<?php unset($__componentOriginal29dcf2c56c04a0abdc193077dccc46e8); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-center text-muted py-4"><?php echo e(__('Объявлений пока нет.')); ?></p>
                    <?php endif; ?>
                </div>
                <div class="vehicle-fullwidth__pagination">
                    <?php if($listings instanceof \Illuminate\Pagination\LengthAwarePaginator || $listings instanceof \Illuminate\Pagination\Paginator): ?>
                        <?php echo e($listings->appends(request()->except('page'))->links()); ?>

                    <?php endif; ?>
                </div>
            </div>
            <aside class="vehicle-fullwidth__sidebar">
                <?php echo $__env->make('listings.partials.vehicle-filter-form', [
                    'mode' => $mode,
                    'bodyOptions' => $bodyOptions,
                    'transmissionOptions' => $transmissionOptions,
                    'fuelOptions' => $fuelOptions,
                    'engineOptions' => $engineOptions,
                    'activeFilters' => $activeFilters,
                    'resetParams' => $resetParams,
                    'resetUrl' => $resetUrl,
                    'fullWidth' => false,
                    'formAction' => $formAction,
                    'formMethod' => $formMethod,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>
        </div>
    </div>
<?php else: ?>
    <div class="brand-surface" data-listings-container>
        <div class="listing-grid<?php echo e($fullWidth ? ' listing-grid--compact' : ''); ?>">
            <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if (isset($component)) { $__componentOriginal29dcf2c56c04a0abdc193077dccc46e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.listing.card','data' => ['listing' => $listing]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('listing.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8)): ?>
<?php $attributes = $__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8; ?>
<?php unset($__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal29dcf2c56c04a0abdc193077dccc46e8)): ?>
<?php $component = $__componentOriginal29dcf2c56c04a0abdc193077dccc46e8; ?>
<?php unset($__componentOriginal29dcf2c56c04a0abdc193077dccc46e8); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-center text-muted py-4"><?php echo e(__('Объявлений пока нет.')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="pt-3" id="ajax-search-results">
        <?php echo e($listings->appends(request()->except('page'))->links()); ?>

    </div>
<?php endif; ?>

<?php echo $__env->make('listings.partials.brand-model-autocomplete-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /var/www/html/resources/views/listings/partials/vehicle-search.blade.php ENDPATH**/ ?>
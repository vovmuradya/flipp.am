<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $onlyRegular = $onlyRegular ?? false;
        $onlyAuctions = $onlyAuctions ?? false;
        $isFullWidth = $onlyRegular || $onlyAuctions;
    ?>
    <section class="brand-section <?php echo e($isFullWidth ? 'brand-section--fullwidth' : ''); ?>">
        <?php if(!$onlyRegular && !$onlyAuctions && $featuredListings->isNotEmpty()): ?>
            <div class="brand-slider brand-slider--fullwidth mt-5" data-slider="auction">
                <div class="brand-slider__header">
                    <div>
                        <h3 class="brand-slider__title"><?php echo e(__('Актуальные автомобили')); ?></h3>
                        <p class="brand-slider__subtitle"><?php echo e(__('Смешанная подборка из аукционов и частных объявлений.')); ?></p>
                    </div>
                </div>

                <div class="brand-slider__viewport" data-slider-viewport>
                    <div class="brand-slider__track" data-slider-track>
                        <?php $__currentLoopData = $featuredListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isAuction = $listing->isFromAuction();
                                $expiresAt = $isAuction ? optional($listing->vehicleDetail)->auction_ends_at : null;
                            ?>
                            <div class="brand-slider__panel">
                                <?php if (isset($component)) { $__componentOriginal29dcf2c56c04a0abdc193077dccc46e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal29dcf2c56c04a0abdc193077dccc46e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.listing.card','data' => ['listing' => $listing,'badge' => $isAuction ? __('Аукцион') : null,'expires' => $expiresAt]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('listing.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isAuction ? __('Аукцион') : null),'expires' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($expiresAt)]); ?>
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
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="brand-slider__nav brand-slider__nav--floating">
                    <button type="button" class="brand-slider__nav-btn" data-slider-prev aria-label="<?php echo e(__('Предыдущие аукционные объявления')); ?>" disabled>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="brand-slider__nav-btn" data-slider-next aria-label="<?php echo e(__('Следующие аукционные объявления')); ?>">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="brand-container <?php echo e($isFullWidth ? 'brand-container--fluid' : ''); ?>">
            <?php if($onlyRegular): ?>
                <?php echo $__env->make('listings.partials.vehicle-search', [
                    'listings' => $listings,
                    'brands' => $brands ?? collect(),
                    'mode' => 'regular',
                    'fullWidth' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($onlyAuctions): ?>
                <?php echo $__env->make('listings.partials.vehicle-search', [
                    'listings' => $listings,
                    'brands' => $brands ?? collect(),
                    'mode' => 'auction',
                    'fullWidth' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="brand-surface">
                    <div class="listing-grid">
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
            <?php endif; ?>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/listings/index.blade.php ENDPATH**/ ?>
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
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title"><?php echo e(__('Мои объявления')); ?></h2>
                <p class="brand-section__subtitle">
                    <?php echo e(__('Управляйте активными и черновыми объявлениями, редактируйте информацию или быстро создавайте новые карточки.')); ?>

                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h3 class="h5 fw-semibold mb-0"><?php echo e(__('Всего объявлений: :count', ['count' => $listings->total()])); ?></h3>
                <a href="<?php echo e(route('listings.create')); ?>" class="btn btn-brand-gradient">
                    + <?php echo e(__('Создать объявление')); ?>

                </a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-lg-4">
                <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col">
                        <div class="card shadow-sm border-0 rounded-3 h-100 overflow-hidden">
                            <?php
                                $fallbackImage = asset('images/no-image.jpg');

                                $resolveMedia = static function (?\Spatie\MediaLibrary\MediaCollections\Models\Media $media, bool $allowConversion = true) {
                                    if (!$media) {
                                        return null;
                                    }

                                    $conversion = null;
                                    if ($allowConversion && method_exists($media, 'hasGeneratedConversion') && $media->hasGeneratedConversion('medium')) {
                                        $conversion = 'medium';
                                    }

                                    try {
                                        $params = ['media' => $media->getKey()];
                                        if ($conversion) {
                                            $params['conversion'] = $conversion;
                                        }

                                        return route('media.show', $params);
                                    } catch (\Throwable $e) {
                                        try {
                                            return $conversion ? $media->getUrl('medium') : $media->getUrl();
                                        } catch (\Throwable $_) {
                                            return null;
                                        }
                                    }
                                };

                                $previewImage = $resolveMedia($listing->getFirstMedia('images'))
                                    ?: $resolveMedia($listing->getFirstMedia('auction_photos'));

                                if (!$previewImage && $listing->vehicleDetail) {
                                    foreach ([
                                                 $listing->vehicleDetail->preview_image_url,
                                                 $listing->vehicleDetail->main_image_url,
                                             ] as $external) {
                                        if (is_string($external) && trim($external) !== '') {
                                            $previewImage = trim($external);
                                            break;
                                        }
                                    }
                                }

                                if (!$previewImage && $listing->image) {
                                    $rawPath = $listing->image;
                                    if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
                                        $previewImage = $rawPath;
                                    } else {
                                        $normalized = ltrim(str_replace('public/', '', $rawPath), '/');
                                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($normalized)) {
                                            $previewImage = \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
                                        } elseif (\Illuminate\Support\Facades\Storage::exists($rawPath)) {
                                            $previewImage = \Illuminate\Support\Facades\Storage::url($rawPath);
                                        }
                                    }
                                }

                                $previewImage = $previewImage ?: $fallbackImage;
                            ?>
                            <div class="ratio ratio-4x3 bg-light overflow-hidden rounded-top">
                                <a href="<?php echo e(route('listings.show', $listing)); ?>" class="d-flex w-100 h-100">
                                    <img src="<?php echo e($previewImage); ?>"
                                         alt="<?php echo e($listing->title); ?>"
                                         class="img-fluid w-100 h-100 object-fit-cover"
                                         loading="lazy"
                                         onerror="this.src='<?php echo e($fallbackImage); ?>'">
                                </a>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <h5 class="card-title fs-6 fw-semibold mb-0 text-truncate" title="<?php echo e($listing->title); ?>">
                                        <?php echo e($listing->title); ?>

                                    </h5>
                                    <span class="badge rounded-pill <?php echo e($listing->status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis'); ?>">
                                        <?php echo e($listing->status === 'active' ? __('Активно') : __('Черновик')); ?>

                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-1">
                                    <?php echo e($listing->region?->localized_name ?? __('Регион не указан')); ?>

                                </p>
                                <p class="card-text fw-semibold mb-2">
                                    <?php echo e(number_format($listing->price, 0, '.', ' ')); ?> <?php echo e($listing->currency); ?>

                                </p>
                                <p class="card-text text-muted small mt-auto mb-3">
                                    <?php echo e(__('Добавлено: :date', ['date' => $listing->created_at->format('d.m.Y')])); ?>

                                </p>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('listings.edit', $listing)); ?>" class="btn btn-sm btn-brand-gradient flex-grow-1">
                                        <?php echo e(__('Редактировать')); ?>

                                    </a>
                                    <form action="<?php echo e(route('listings.destroy', $listing)); ?>" method="POST" class="flex-grow-1" onsubmit="return confirm('<?php echo e(__('Удалить объявление?')); ?>');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                            <?php echo e(__('Удалить')); ?>

                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="brand-surface text-center py-5 text-muted">
                            <?php echo e(__('У вас пока нет объявлений. Нажмите «Создать объявление», чтобы добавить первое.')); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pt-4">
                <?php echo e($listings->links()); ?>

            </div>
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
<?php /**PATH /var/www/html/resources/views/dashboard/my-listings.blade.php ENDPATH**/ ?>
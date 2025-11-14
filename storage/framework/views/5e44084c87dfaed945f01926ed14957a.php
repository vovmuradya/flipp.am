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
                <h2 class="brand-section__title"><?php echo e(__('Авто с аукционов Copart')); ?></h2>
                <p class="brand-section__subtitle">
                    <?php echo e(__('Просматривайте и отслеживайте все импортированные лоты. Следите за статусом и переходите к подробному описанию в один клик.')); ?>

                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h3 class="h5 fw-semibold mb-0"><?php echo e(__('Найдено :count лотов', ['count' => $listings->total()])); ?></h3>
                <a href="<?php echo e(route('listings.create-from-auction')); ?>" class="btn btn-brand-outline">
                    + <?php echo e(__('Добавить из Copart')); ?>

                </a>
            </div>

            <div class="row gy-4">
                <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $endsAt = optional($listing->vehicleDetail)->auction_ends_at;
                        $endsAtLocal = $endsAt?->timezone(config('app.timezone'));
                        $endsAtIso = $endsAtLocal?->toIso8601String();
                        $statusLabel = __('Active');
                        $statusClass = 'bg-success-subtle text-success-emphasis';

                        if ($endsAt instanceof \Carbon\Carbon) {
                            if ($endsAt->isPast()) {
                                $statusLabel = __('Ended');
                                $statusClass = 'bg-secondary-subtle text-secondary-emphasis';
                            } elseif ($endsAt->diffInHours(now()) >= 24) {
                                $statusLabel = __('Upcoming');
                                $statusClass = 'bg-warning-subtle text-warning-emphasis';
                            } else {
                                $statusLabel = __('Active');
                                $statusClass = 'bg-success-subtle text-success-emphasis';
                            }
                        }

                        $fallbackImage = asset('images/no-image.jpg');
                        $previewImage = $listing->getFirstMediaUrl('images', 'medium')
                            ?: $listing->getFirstMediaUrl('images')
                            ?: $listing->getFirstMediaUrl('auction_photos', 'medium')
                            ?: $listing->getFirstMediaUrl('auction_photos');

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

                        $previewImage = $previewImage ?: $fallbackImage;
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="auction-card" id="auction-card-<?php echo e($listing->id); ?>" data-listing-card="<?php echo e($listing->id); ?>">
                            <span class="auction-card__badge <?php echo e($statusClass); ?>">
                                <?php echo e($statusLabel); ?>

                            </span>
                            <a href="<?php echo e(route('listings.show', $listing)); ?>" class="auction-card__image">
                                <img src="<?php echo e($previewImage); ?>" alt="<?php echo e($listing->title); ?>" loading="lazy" onerror="this.src='<?php echo e($fallbackImage); ?>'">
                            </a>
                            <div class="auction-card__body">
                                <div class="auction-card__heading">
                                    <h5 title="<?php echo e($listing->title); ?>"><?php echo e($listing->title); ?></h5>
                                    <span class="auction-card__lot"><?php echo e(__('Лот № :id', ['id' => $listing->id])); ?></span>
                                </div>
                                <ul class="auction-card__meta">
                                    <li>
                                        <span><?php echo e(__('Окончание')); ?></span>
                                        <?php if($endsAtIso): ?>
                                            <strong class="d-flex flex-column align-items-end gap-1 text-end">
                                                <span
                                                    data-countdown
                                                    data-expires="<?php echo e($endsAtIso); ?>"
                                                    data-prefix="<?php echo e(__('До конца')); ?>"
                                                    data-expired-text="<?php echo e(__('Лот завершён')); ?>"
                                                    data-day-label="<?php echo e(__('д')); ?>"
                                                >
                                                    <span data-countdown-text><?php echo e(__('Загрузка…')); ?></span>
                                                </span>
                                                <small class="text-muted"><?php echo e($endsAtLocal->format('d.m.Y H:i')); ?></small>
                                            </strong>
                                        <?php else: ?>
                                            <strong><?php echo e(__('Не указано')); ?></strong>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <span><?php echo e(__('Ставка')); ?></span>
                                        <strong><?php echo e(number_format($listing->price, 0, '.', ' ')); ?> <?php echo e($listing->currency); ?></strong>
                                    </li>
                                </ul>
                                <div class="auction-card__actions">
                                    <a href="<?php echo e(route('listings.show', $listing)); ?>" class="btn btn-brand-gradient">
                                        <?php echo e(__('Подробнее')); ?>

                                    </a>
                                    <?php if(auth()->id() === $listing->user_id): ?>
                                        <a href="<?php echo e(route('auction-listings.edit', $listing)); ?>" class="btn btn-outline-secondary">
                                            <?php echo e(__('Редактировать')); ?>

                                        </a>
                                        <form action="<?php echo e(route('auction-listings.destroy', $listing)); ?>"
                                              method="POST"
                                              data-auction-delete
                                              data-listing-card="auction-card-<?php echo e($listing->id); ?>"
                                              data-confirm="<?php echo e(__('Удалить это объявление?')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger">
                                                <?php echo e(__('Удалить')); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="brand-surface text-center py-5 text-muted">
                        <?php echo e(__('По заданным параметрам ничего не найдено. Попробуйте изменить фильтры.')); ?>

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

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            document.querySelectorAll('[data-auction-delete]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.pending === 'true') {
                        event.preventDefault();
                        return;
                    }

                    const message = form.dataset.confirm || '';
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                        return;
                    }

                    if (!window.fetch || !token) {
                        return;
                    }

                    event.preventDefault();
                    form.dataset.pending = 'true';

                    const payload = new URLSearchParams(new FormData(form)).toString();

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        },
                        body: payload,
                    }).then((response) => {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }

                        const cardId = form.dataset.listingCard;
                        const card = cardId ? document.getElementById(cardId) : form.closest('[data-listing-card]');
                        if (card) {
                            card.classList.add('auction-card--removed');
                            setTimeout(() => card.remove(), 250);
                        }
                    }).catch(() => {
                        delete form.dataset.pending;
                        form.submit();
                    });
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/dashboard/my-auctions.blade.php ENDPATH**/ ?>
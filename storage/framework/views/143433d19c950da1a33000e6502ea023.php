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
                <h2 class="brand-section__title"><?php echo e(__('Импорт автомобиля с аукциона')); ?></h2>
                <p class="brand-section__subtitle">
                    <?php echo e(__('Вставьте ссылку на лот Copart — мы автоматически подготовим черновик объявления и поможем ускорить публикацию.')); ?>

                </p>
            </div>

            <div class="brand-surface">
                <div class="row g-5 align-items-start">
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <span class="badge rounded-pill text-bg-light text-uppercase fw-semibold"><?php echo e(__('Шаг 1 из 2')); ?></span>
                                <h3 class="h4 fw-semibold mt-2 mb-0"><?php echo e(__('Укажите ссылку на аукционный лот')); ?></h3>
                                <p class="text-muted mb-0">
                                    <?php echo e(__('После отправки мы подтянем характеристики, фото и стоимость — останется только проверить данные и опубликовать.')); ?>

                                </p>
                            </div>

                            <form id="auction-import-form" method="POST" action="<?php echo e(route('listings.import-auction')); ?>" class="d-flex flex-column gap-3" novalidate>
                                <?php echo csrf_field(); ?>
                                <div class="d-flex flex-column gap-2">
                                    <label for="auction-url" class="form-label fw-semibold mb-0"><?php echo e(__('Ссылка на лот с аукциона')); ?></label>
                                    <input
                                        type="url"
                                        id="auction-url"
                                        name="auction_url"
                                        class="form-control form-control-lg"
                                        placeholder="https://www.copart.com/lot/..."
                                        value="<?php echo e(old('auction_url')); ?>"
                                        required
                                    >
                                    <div class="form-text"><?php echo e(__('Поддерживаемая площадка:')); ?> <span class="fw-semibold">Copart.com</span></div>
                                </div>

                                <div id="auction-url-client-error" class="alert alert-danger mb-0 d-none" role="alert"></div>

                                <?php if($errors->has('auction_url')): ?>
                                    <div class="alert alert-danger mb-0">
                                        <?php echo e($errors->first('auction_url')); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(session('auction_error')): ?>
                                    <div class="alert alert-warning mb-0">
                                        <?php echo e(session('auction_error')); ?>

                                    </div>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap gap-3 pt-1">
                                    <button type="submit" class="btn btn-brand-gradient btn-lg px-4"><?php echo e(__('Импортировать')); ?></button>
                                    <a href="<?php echo e(route('home')); ?>" class="btn btn-brand-outline btn-lg px-4"><?php echo e(__('Отмена')); ?></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100 d-flex flex-column gap-4" style="background: rgba(17,24,39,0.04); border: 1px solid rgba(17,24,39,0.08);">
                            <div>
                                <h4 class="h5 fw-semibold mb-3"><?php echo e(__('Что произойдет дальше?')); ?></h4>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">1</span>
                                        <div>
                                            <span class="fw-semibold d-block"><?php echo e(__('Мы парсим лот за несколько секунд')); ?></span>
                                            <span class="text-muted"><?php echo e(__('Загружаем характеристики, историю пробега и фотографии, если они доступны на площадке.')); ?></span>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">2</span>
                                        <div>
                                            <span class="fw-semibold d-block"><?php echo e(__('Проверьте и дополните данные')); ?></span>
                                            <span class="text-muted"><?php echo e(__('Система перенаправит на форму объявления, где можно скорректировать описание, цену и контакты.')); ?></span>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">3</span>
                                        <div>
                                            <span class="fw-semibold d-block"><?php echo e(__('Публикуйте в один клик')); ?></span>
                                            <span class="text-muted"><?php echo e(__('После проверки сохраните объявление в черновики или сразу опубликуйте на сайте.')); ?></span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-3 rounded-4" style="background: rgba(244,140,37,0.12); border: 1px solid rgba(244,140,37,0.2);">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="fs-4">💡</span>
                                    <div>
                                        <p class="mb-1 fw-semibold text-dark"><?php echo e(__('Лот ещё не опубликован?')); ?></p>
                                        <p class="mb-0 text-muted"><?php echo e(__('Можно импортировать черновик и вернуться к заполнению позже — он сохранится в разделе «Мои объявления».')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('auction-import-form');
                const urlInput = document.getElementById('auction-url');
                const clientError = document.getElementById('auction-url-client-error');
                if (!form || !urlInput || !clientError) {
                    return;
                }

                const allowedHosts = [
                    'copart.com'
                ];
                const messages = {
                    invalidUrl: <?php echo json_encode(__('Некорректный адрес ссылки. Проверьте формат URL и попробуйте снова.'), 15, 512) ?>,
                    unsupportedHost: <?php echo json_encode(__('Поддерживаются только ссылки с аукциона Copart.'), 15, 512) ?>,
                };

                form.addEventListener('submit', (event) => {
                    clientError.classList.add('d-none');
                    clientError.textContent = '';

                    const rawUrl = urlInput.value.trim();
                    if (!rawUrl) {
                        return;
                    }

                    let parsedHost;
                    try {
                        parsedHost = new URL(rawUrl).hostname.toLowerCase();
                    } catch (error) {
                        event.preventDefault();
                        clientError.textContent = messages.invalidUrl;
                        clientError.classList.remove('d-none');
                        return;
                    }

                    const isAllowed = allowedHosts.some((domain) => {
                        return parsedHost === domain || parsedHost.endsWith(`.${domain}`);
                    });

                    if (!isAllowed) {
                        event.preventDefault();
                        clientError.textContent = messages.unsupportedHost;
                        clientError.classList.remove('d-none');
                    }
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/listings/create-from-auction.blade.php ENDPATH**/ ?>
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
    <section class="brand-section create-choice">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title"><?php echo e(__('Какое объявление вы хотите разместить?')); ?></h2>
                <p class="brand-section__subtitle">
                    <?php echo e(__('Выберите подходящий формат и заполните форму, чтобы ваше объявление появилось на idrom.am.')); ?>

                </p>
            </div>
            <div class="create-choice__grid">
                <a href="<?php echo e(route('listings.create')); ?>" class="create-choice__card">
                    <div class="create-choice__icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3 class="create-choice__card-title"><?php echo e(__('Обычное объявление')); ?></h3>
                    <p class="create-choice__card-text">
                        <?php echo e(__('Подходит для частных продавцов и автосалонов. Добавьте автомобиль, запчасти или шины с подробным описанием и фото.')); ?>

                    </p>
                    <span class="create-choice__cta">
                        <?php echo e(__('Перейти к форме')); ?>

                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="<?php echo e(route('listings.create-from-auction')); ?>" class="create-choice__card create-choice__card--auction">
                    <div class="create-choice__icon">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                    <h3 class="create-choice__card-title"><?php echo e(__('Объявление из аукциона')); ?></h3>
                    <p class="create-choice__card-text">
                        <?php echo e(__('Импортируйте данные по лоту Copart и быстро создайте объявление с уже заполненными характеристиками.')); ?>

                    </p>
                    <span class="create-choice__cta">
                        <?php echo e(__('Перейти к импорту')); ?>

                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
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
<?php /**PATH /var/www/html/resources/views/listings/create-choice.blade.php ENDPATH**/ ?>
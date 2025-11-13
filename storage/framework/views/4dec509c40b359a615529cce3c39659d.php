<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Bootstrap & Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
<body class="auth-body font-sans antialiased">
        <div class="auth-layout">
            <div class="auth-layout__aside">
                <a class="auth-brand" href="<?php echo e(route('home')); ?>">
                    <span class="auth-brand__logo">
                        <i class="fa-solid fa-car-side"></i>
                    </span>
                    <span class="auth-brand__text">idrom.am</span>
                </a>

                <div class="auth-aside__content">
                    <h1 class="auth-aside__title"><?php echo e(__('Место, где автомобили находят новых владельцев')); ?></h1>
                    <p class="auth-aside__subtitle">
                        <?php echo e(__('Публикуйте объявления, следите за лотами с аукционов и общайтесь с покупателями — всё в одном профиле.')); ?>

                    </p>

                    <ul class="auth-aside__list">
                        <li><i class="fa-solid fa-check"></i> <?php echo e(__('Быстрая подача объявления с фото и характеристиками')); ?></li>
                        <li><i class="fa-solid fa-check"></i> <?php echo e(__('Уведомления о сообщениях и избранных предложениях')); ?></li>
                        <li><i class="fa-solid fa-check"></i> <?php echo e(__('Инструменты для частных продавцов, дилеров и сервисов')); ?></li>
                    </ul>
                </div>
            </div>

            <div class="auth-layout__main">
                <div class="auth-card">
                    <?php echo e($slot); ?>

                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <?php echo $__env->yieldPushContent('scripts'); ?>
        <?php echo $__env->make('layouts.partials.locale-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/guest.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- App icons -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="alternate icon" href="<?php echo e(asset('images/logo.png')); ?>">

    <!-- Styles -->
    <?php echo $__env->yieldPushContent('styles'); ?>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="font-sans antialiased" style="background-color: var(--brand-light-gray);">
<div class="min-h-screen">
    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Page Heading -->
    <?php if(isset($header)): ?>
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <?php echo e($header); ?>

            </div>
        </header>
    <?php endif; ?>

    <!-- Page Content -->
    <main>
        <?php if (! empty(trim($__env->yieldContent('content')))): ?>
            <?php echo $__env->yieldContent('content'); ?>
        <?php else: ?>
            <?php echo e($slot ?? ''); ?>

        <?php endif; ?>
    </main>

    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php echo $__env->make('layouts.partials.locale-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.auction.countdown-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->yieldPushContent('scripts'); ?>

<!-- Alpine.js fallback -->
<script>
    (function () {
        function loadAlpineFallback() {
            if (window.Alpine) return;

            const script = document.createElement('script');
            script.defer = true;
            script.src = "<?php echo e(asset('vendor/alpinejs/alpine-3.15.0.min.js')); ?>";
            script.addEventListener('load', function () {
                if (window.Alpine && typeof window.Alpine.start === 'function') {
                    window.Alpine.start();
                }
            });
            document.head.appendChild(script);
        }

        function scheduleFallback() {
            window.setTimeout(loadAlpineFallback, 200);
        }

        if (window.Alpine) {
            return;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleFallback);
        } else {
            scheduleFallback();
        }
    })();
</script>

</body>
</html>
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/layouts/app.blade.php ENDPATH**/ ?>
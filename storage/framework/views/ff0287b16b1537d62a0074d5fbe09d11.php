<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <section class="auth-card__section">
        <header class="auth-card__header">
            <h2 class="auth-card__title"><?php echo e(__('Войти в аккаунт')); ?></h2>
            <p class="auth-card__subtitle">
                <?php echo e(__('Управляйте объявлениями, переписывайтесь с покупателями и отслеживайте отклики в личном кабинете idrom.am.')); ?>

            </p>
        </header>

        <?php if(session('status')): ?>
            <div class="auth-alert auth-alert--success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo e(session('status')); ?></span>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo e(__('Проверьте введённые данные и попробуйте снова.')); ?></span>
            </div>
        <?php endif; ?>

        <?php $__errorArgs = ['provider'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo e($message); ?></span>
            </div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <div class="auth-social">
            <a href="<?php echo e(route('auth.provider.redirect', 'google')); ?>" class="btn-social btn-social--google">
                <i class="fa-brands fa-google"></i>
                <?php echo e(__('Войти через Google')); ?>

            </a>
            <a href="<?php echo e(route('auth.provider.redirect', 'facebook')); ?>" class="btn-social btn-social--facebook">
                <i class="fa-brands fa-facebook-f"></i>
                <?php echo e(__('Войти через Facebook')); ?>

            </a>
        </div>

        <div class="auth-divider">
            <span><?php echo e(__('или')); ?></span>
        </div>

        <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form">
            <?php echo csrf_field(); ?>

            <div class="auth-form__field">
                <label for="email" class="auth-form__label">Email</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-envelope"></i></span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="auth-input__control"
                    >
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="auth-form__error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="auth-form__field">
                <label for="password" class="auth-form__label"><?php echo e(__('Пароль')); ?></label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="<?php echo e(__('Введите пароль')); ?>"
                        class="auth-input__control"
                    >
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="auth-form__error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="auth-form__options">
                <label class="auth-checkbox">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        <?php echo e(old('remember') ? 'checked' : ''); ?>

                    >
                    <span><?php echo e(__('Запомнить меня')); ?></span>
                </label>

                <?php if(Route::has('password.request')): ?>
                    <a href="<?php echo e(route('password.request')); ?>" class="auth-link"><?php echo e(__('Забыли пароль?')); ?></a>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-brand-red btn-brand-full auth-form__submit">
                <?php echo e(__('Войти')); ?>

            </button>
        </form>

        <p class="auth-card__switch">
            <?php echo e(__('Нет аккаунта?')); ?>

            <a href="<?php echo e(route('register')); ?>"><?php echo e(__('Зарегистрироваться')); ?></a>
        </p>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>
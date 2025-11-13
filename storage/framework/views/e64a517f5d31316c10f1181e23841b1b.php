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
            <h2 class="auth-card__title"><?php echo e(__('Создать аккаунт')); ?></h2>
            <p class="auth-card__subtitle">
                <?php echo e(__('Регистрируйтесь и публикуйте автомобили, запчасти и шины, следите за сообщениями и избранными объявлениями.')); ?>

            </p>
        </header>

        <?php if($errors->any()): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo e(__('Не удалось сохранить форму. Исправьте ошибки и попробуйте снова.')); ?></span>
            </div>
        <?php endif; ?>

        <div class="auth-social">
            <a href="<?php echo e(route('auth.provider.redirect', 'google')); ?>" class="btn-social btn-social--google">
                <i class="fa-brands fa-google"></i>
                <?php echo e(__('Зарегистрироваться через Google')); ?>

            </a>
            <a href="<?php echo e(route('auth.provider.redirect', 'facebook')); ?>" class="btn-social btn-social--facebook">
                <i class="fa-brands fa-facebook-f"></i>
                <?php echo e(__('Зарегистрироваться через Facebook')); ?>

            </a>
        </div>

        <div class="auth-divider">
            <span><?php echo e(__('или заполните форму')); ?></span>
        </div>

        <form method="POST" action="<?php echo e(route('register')); ?>" class="auth-form">
            <?php echo csrf_field(); ?>

            <div class="auth-form__field">
                <label for="name" class="auth-form__label"><?php echo e(__('Имя')); ?></label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-user"></i></span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="<?php echo e(old('name')); ?>"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="<?php echo e(__('Как к вам обращаться')); ?>"
                        class="auth-input__control"
                    >
                </div>
                <?php $__errorArgs = ['name'];
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
                <label for="email" class="auth-form__label">Email</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-envelope"></i></span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        required
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
                <label for="phone" class="auth-form__label"><?php echo e(__('Телефон')); ?></label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-phone"></i></span>
                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="<?php echo e(old('phone')); ?>"
                        required
                        autocomplete="tel"
                        placeholder="+374 00 00 00"
                        class="auth-input__control"
                    >
                </div>
                <div class="auth-form__actions mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="sendPhoneCode">
                        <?php echo e(__('Получить код')); ?>

                    </button>
                    <small id="phoneCodeStatus" class="text-muted ms-2"></small>
                </div>
                <?php $__errorArgs = ['phone'];
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
                <label for="verification_code" class="auth-form__label"><?php echo e(__('Код подтверждения')); ?></label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-shield-keyhole"></i></span>
                    <input
                        id="verification_code"
                        type="text"
                        name="verification_code"
                        value="<?php echo e(old('verification_code')); ?>"
                        required
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        placeholder="123456"
                        class="auth-input__control"
                    >
                </div>
                <?php $__errorArgs = ['verification_code'];
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
                        autocomplete="new-password"
                        placeholder="<?php echo e(__('Минимум 8 символов')); ?>"
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

            <div class="auth-form__field">
                <label for="password_confirmation" class="auth-form__label"><?php echo e(__('Повторите пароль')); ?></label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="<?php echo e(__('Ещё раз пароль')); ?>"
                        class="auth-input__control"
                    >
                </div>
                <?php $__errorArgs = ['password_confirmation'];
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

            <button type="submit" class="btn-brand-gradient btn-brand-full auth-form__submit">
                <?php echo e(__('Зарегистрироваться')); ?>

            </button>
        </form>

        <p class="auth-card__switch">
            <?php echo e(__('Уже есть аккаунт?')); ?>

            <a href="<?php echo e(route('login')); ?>"><?php echo e(__('Войти')); ?></a>
        </p>
    </section>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sendBtn = document.getElementById('sendPhoneCode');
                const phoneInput = document.getElementById('phone');
                const statusEl = document.getElementById('phoneCodeStatus');

                if (!sendBtn || !phoneInput) {
                    return;
                }

                const messages = {
                    success: <?php echo json_encode(__('Код отправлен. Пожалуйста, проверьте SMS.'), 512) ?>,
                    error: <?php echo json_encode(__('Не удалось отправить код. Попробуйте ещё раз.'), 15, 512) ?>,
                    empty: <?php echo json_encode(__('Введите номер телефона.'), 15, 512) ?>,
                };

                sendBtn.addEventListener('click', async () => {
                    const phone = phoneInput.value.trim();
                    if (!phone) {
                        statusEl.textContent = messages.empty;
                        statusEl.classList.add('text-danger');
                        return;
                    }

                    sendBtn.disabled = true;
                    statusEl.textContent = '';

                    try {
                        const response = await fetch('<?php echo e(route('auth.phone.send-code')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({ phone }),
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            statusEl.textContent = data.message || messages.success;
                            statusEl.classList.remove('text-danger');
                            statusEl.classList.add('text-success');
                        } else {
                            throw new Error(data.message || messages.error);
                        }
                    } catch (error) {
                        statusEl.textContent = error.message || messages.error;
                        statusEl.classList.remove('text-success');
                        statusEl.classList.add('text-danger');
                    } finally {
                        sendBtn.disabled = false;
                    }
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH /var/www/html/resources/views/auth/register.blade.php ENDPATH**/ ?>
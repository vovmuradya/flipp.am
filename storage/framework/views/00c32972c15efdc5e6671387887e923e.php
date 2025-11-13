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
     <?php $__env->slot('header', null, []); ?> 
        <div class="brand-container py-6">
            <p class="profile-page__eyebrow"><?php echo e(__('Личный кабинет')); ?></p>
            <h1 class="profile-page__title"><?php echo e(__('Настройки профиля')); ?></h1>
            <p class="profile-page__subtitle">
                <?php echo e(__('Обновите контактные данные, подтвердите номер телефона и управляйте безопасностью аккаунта.')); ?>

            </p>
        </div>
     <?php $__env->endSlot(); ?>

    <section class="brand-section profile-page">
        <div class="brand-container space-y-6">
            <?php if(session('error')): ?>
                <div class="profile-alert profile-alert--error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <p class="mb-0"><?php echo e(session('error')); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="profile-page__hero brand-surface">
                <div>
                    <p class="profile-page__hero-label"><?php echo e(__('Вы авторизованы как')); ?></p>
                    <h2 class="profile-page__hero-title"><?php echo e($user->name); ?></h2>
                    <p class="profile-page__hero-subtitle"><?php echo e($user->email); ?></p>
                </div>
                <div class="profile-pill-group">
                    <span class="profile-pill <?php echo e($user->phone_verified_at ? 'profile-pill--success' : 'profile-pill--muted'); ?>">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo e($user->phone_verified_at ? __('Телефон подтверждён') : __('Телефон не подтверждён')); ?>

                    </span>
                    <span class="profile-pill profile-pill--muted">
                        <i class="fa-solid fa-circle-user"></i>
                        ID <?php echo e($user->id); ?>

                    </span>
                </div>
            </div>

            <div class="profile-page__grid">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow"><?php echo e(__('Основная информация')); ?></p>
                            <h3 class="profile-card__title"><?php echo e(__('Контактные данные')); ?></h3>
                        </div>
                        <?php if(session('status') === 'profile-updated'): ?>
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                <?php echo e(__('Сохранено')); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?php echo e(route('profile.update')); ?>" class="profile-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('patch'); ?>

                        <div class="profile-form__group">
                            <label for="profile_name" class="brand-form-label"><?php echo e(__('Имя и фамилия')); ?></label>
                            <input
                                type="text"
                                id="profile_name"
                                name="name"
                                value="<?php echo e(old('name', $user->name)); ?>"
                                class="brand-form-control"
                                required
                                autocomplete="name"
                            >
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="profile-form__error"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="profile-form__group">
                            <label for="profile_email" class="brand-form-label"><?php echo e(__('Электронная почта')); ?></label>
                            <input
                                type="email"
                                id="profile_email"
                                name="email"
                                value="<?php echo e(old('email', $user->email)); ?>"
                                class="brand-form-control"
                                required
                                autocomplete="username"
                            >
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="profile-form__error"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                <?php echo e(__('Сохранить изменения')); ?>

                            </button>
                        </div>
                    </form>

                    <form id="send-verification" method="post" action="<?php echo e(route('verification.send')); ?>" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>

                    <?php if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()): ?>
                        <div class="profile-alert profile-alert--warning mt-4">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <p class="mb-1"><?php echo e(__('Почта не подтверждена. Письмо с ссылкой придёт в течение пары минут.')); ?></p>
                                <button form="send-verification" type="submit" class="btn-brand-outline btn-sm">
                                    <?php echo e(__('Отправить ссылку ещё раз')); ?>

                                </button>
                                <?php if(session('status') === 'verification-link-sent'): ?>
                                    <p class="profile-alert__note"><?php echo e(__('Новая ссылка подтверждения отправлена на почту.')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif(session('status') === 'verification-link-sent'): ?>
                        <div class="profile-alert profile-alert--success mt-4">
                            <i class="fa-solid fa-check"></i>
                            <p class="mb-0"><?php echo e(__('Новая ссылка подтверждения отправлена на почту.')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="brand-surface profile-card" data-phone-verification>
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow"><?php echo e(__('Безопасность объявлений')); ?></p>
                            <h3 class="profile-card__title"><?php echo e(__('Подтверждение телефона')); ?></h3>
                        </div>
                        <span class="profile-pill <?php echo e($user->phone_verified_at ? 'profile-pill--success' : 'profile-pill--muted'); ?>">
                            <?php echo e($user->phone_verified_at ? __('Подтверждён') : __('Не подтверждён')); ?>

                        </span>
                    </div>

                    <?php if(session('phone_status')): ?>
                        <div class="profile-alert profile-alert--success mb-4">
                            <i class="fa-solid fa-circle-check"></i>
                            <p class="mb-0"><?php echo e(session('phone_status')); ?></p>
                        </div>
                    <?php endif; ?>

                    <form
                        method="post"
                        action="<?php echo e(route('profile.phone.verify')); ?>"
                        class="profile-form"
                        data-phone-form
                        data-send-url="<?php echo e(route('auth.phone.send-code')); ?>"
                        data-text-empty="<?php echo e(__('Введите номер телефона перед отправкой кода.')); ?>"
                        data-text-sending="<?php echo e(__('Отправляем...')); ?>"
                        data-text-success="<?php echo e(__('Код отправлен. Проверьте SMS.')); ?>"
                        data-text-error="<?php echo e(__('Произошла ошибка. Попробуйте позже.')); ?>"
                        data-cooldown-seconds="60"
                        data-text-cooldown="<?php echo e(__('Повторная отправка через :seconds с', ['seconds' => ':seconds'])); ?>"
                    >
                        <?php echo csrf_field(); ?>

                        <div class="profile-form__group">
                            <label for="profile_phone" class="brand-form-label"><?php echo e(__('Номер телефона')); ?></label>
                            <div class="profile-phone-field">
                                <input
                                    type="tel"
                                    id="profile_phone"
                                    name="phone"
                                    value="<?php echo e(old('phone', $user->phone)); ?>"
                                    class="brand-form-control"
                                    placeholder="+374 00 000 000"
                                >
                                <button type="button" class="btn-brand-outline profile-phone-send" data-phone-send>
                                    <?php echo e(__('Отправить код')); ?>

                                </button>
                            </div>
                            <p class="profile-form__hint" data-phone-status>
                                <?php echo e(__('Мы отправим 6-значный код в SMS. Первые 10 минут код будет активен.')); ?>

                            </p>
                            <?php if($errors->phoneVerification->has('phone')): ?>
                                <p class="profile-form__error"><?php echo e($errors->phoneVerification->first('phone')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="profile-form__group">
                            <label for="profile_phone_code" class="brand-form-label"><?php echo e(__('Код подтверждения')); ?></label>
                            <input
                                type="text"
                                id="profile_phone_code"
                                name="verification_code"
                                class="brand-form-control"
                                maxlength="6"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                placeholder="123456"
                            >
                            <?php if($errors->phoneVerification->has('verification_code')): ?>
                                <p class="profile-form__error"><?php echo e($errors->phoneVerification->first('verification_code')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                <?php echo e(__('Подтвердить номер')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-page__grid profile-page__grid--stacked">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow"><?php echo e(__('Безопасность')); ?></p>
                            <h3 class="profile-card__title"><?php echo e(__('Смена пароля')); ?></h3>
                        </div>
                        <?php if(session('status') === 'password-updated'): ?>
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                <?php echo e(__('Обновлено')); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="<?php echo e(route('password.update')); ?>" class="profile-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>

                        <div class="profile-form__group">
                            <label for="current_password" class="brand-form-label"><?php echo e(__('Текущий пароль')); ?></label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            <?php if($errors->updatePassword->has('current_password')): ?>
                                <p class="profile-form__error"><?php echo e($errors->updatePassword->first('current_password')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="profile-form__grid">
                            <div>
                                <label for="new_password" class="brand-form-label"><?php echo e(__('Новый пароль')); ?></label>
                                <input
                                    type="password"
                                    id="new_password"
                                    name="password"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                <?php if($errors->updatePassword->has('password')): ?>
                                    <p class="profile-form__error"><?php echo e($errors->updatePassword->first('password')); ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="brand-form-label"><?php echo e(__('Повторите пароль')); ?></label>
                                <input
                                    type="password"
                                    id="new_password_confirmation"
                                    name="password_confirmation"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                <?php if($errors->updatePassword->has('password_confirmation')): ?>
                                    <p class="profile-form__error"><?php echo e($errors->updatePassword->first('password_confirmation')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-outline">
                                <?php echo e(__('Сохранить пароль')); ?>

                            </button>
                        </div>
                    </form>
                </div>

                <div class="brand-surface profile-card profile-card--danger">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow"><?php echo e(__('Опасная зона')); ?></p>
                            <h3 class="profile-card__title"><?php echo e(__('Удаление аккаунта')); ?></h3>
                        </div>
                    </div>

                    <p class="profile-card__text">
                        <?php echo e(__('После удаления аккаунта восстановить данные будет невозможно. Скачайте важную информацию заранее и подтвердите действие паролем.')); ?>

                    </p>

                    <form method="post" action="<?php echo e(route('profile.destroy')); ?>" class="profile-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('delete'); ?>

                        <div class="profile-form__group">
                            <label for="delete_password" class="brand-form-label"><?php echo e(__('Пароль для подтверждения')); ?></label>
                            <input
                                type="password"
                                id="delete_password"
                                name="password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            <?php if($errors->userDeletion->has('password')): ?>
                                <p class="profile-form__error"><?php echo e($errors->userDeletion->first('password')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="profile-form__actions">
                            <button
                                type="submit"
                                class="btn-brand-red btn-brand-full"
                                onclick="return confirm('<?php echo e(__('Вы уверены, что хотите удалить аккаунт?')); ?>')"
                            >
                                <?php echo e(__('Удалить аккаунт')); ?>

                            </button>
                        </div>
                    </form>
                </div>
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
<?php /**PATH /var/www/html/resources/views/profile/edit.blade.php ENDPATH**/ ?>
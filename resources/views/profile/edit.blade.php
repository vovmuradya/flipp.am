<x-app-layout>
    <x-slot name="header">
        <div class="brand-container py-6">
            <p class="profile-page__eyebrow">{{ __('Личный кабинет') }}</p>
            <h1 class="profile-page__title">{{ __('Настройки профиля') }}</h1>
            <p class="profile-page__subtitle">
                {{ __('Обновите контактные данные, подтвердите номер телефона и управляйте безопасностью аккаунта.') }}
            </p>
        </div>
    </x-slot>

    <section class="brand-section profile-page">
        <div class="brand-container space-y-6">
            @if (session('error'))
                <div class="profile-alert profile-alert--error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="profile-page__hero brand-surface">
                <div>
                    <p class="profile-page__hero-label">{{ __('Вы авторизованы как') }}</p>
                    <h2 class="profile-page__hero-title">{{ $user->name }}</h2>
                    <p class="profile-page__hero-subtitle">{{ $user->email }}</p>
                </div>
                <div class="profile-pill-group">
                    <span class="profile-pill {{ $user->phone_verified_at ? 'profile-pill--success' : 'profile-pill--muted' }}">
                        <i class="fa-solid fa-phone"></i>
                        {{ $user->phone_verified_at ? __('Телефон подтверждён') : __('Телефон не подтверждён') }}
                    </span>
                    <span class="profile-pill profile-pill--muted">
                        <i class="fa-solid fa-circle-user"></i>
                        ID {{ $user->id }}
                    </span>
                </div>
            </div>

            <div class="profile-page__grid">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('Основная информация') }}</p>
                            <h3 class="profile-card__title">{{ __('Контактные данные') }}</h3>
                        </div>
                        @if (session('status') === 'profile-updated')
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ __('Сохранено') }}
                            </span>
                        @endif
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
                        @csrf
                        @method('patch')

                        <div class="profile-form__group">
                            <label for="profile_name" class="brand-form-label">{{ __('Имя и фамилия') }}</label>
                            <input
                                type="text"
                                id="profile_name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="brand-form-control"
                                required
                                autocomplete="name"
                            >
                            @error('name')
                                <p class="profile-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="profile-form__group">
                            <label for="profile_email" class="brand-form-label">{{ __('Электронная почта') }}</label>
                            <input
                                type="email"
                                id="profile_email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="brand-form-control"
                                required
                                autocomplete="username"
                            >
                            @error('email')
                                <p class="profile-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                {{ __('Сохранить изменения') }}
                            </button>
                        </div>
                    </form>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
                        @csrf
                    </form>

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="profile-alert profile-alert--warning mt-4">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <p class="mb-1">{{ __('Почта не подтверждена. Письмо с ссылкой придёт в течение пары минут.') }}</p>
                                <button form="send-verification" type="submit" class="btn-brand-outline btn-sm">
                                    {{ __('Отправить ссылку ещё раз') }}
                                </button>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="profile-alert__note">{{ __('Новая ссылка подтверждения отправлена на почту.') }}</p>
                                @endif
                            </div>
                        </div>
                    @elseif (session('status') === 'verification-link-sent')
                        <div class="profile-alert profile-alert--success mt-4">
                            <i class="fa-solid fa-check"></i>
                            <p class="mb-0">{{ __('Новая ссылка подтверждения отправлена на почту.') }}</p>
                        </div>
                    @endif
                </div>

                <div class="brand-surface profile-card" data-phone-verification>
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('Безопасность объявлений') }}</p>
                            <h3 class="profile-card__title">{{ __('Подтверждение телефона') }}</h3>
                        </div>
                        <span class="profile-pill {{ $user->phone_verified_at ? 'profile-pill--success' : 'profile-pill--muted' }}">
                            {{ $user->phone_verified_at ? __('Подтверждён') : __('Не подтверждён') }}
                        </span>
                    </div>

                    @if (session('phone_status'))
                        <div class="profile-alert profile-alert--success mb-4">
                            <i class="fa-solid fa-circle-check"></i>
                            <p class="mb-0">{{ session('phone_status') }}</p>
                        </div>
                    @endif

                    <form
                        method="post"
                        action="{{ route('profile.phone.verify') }}"
                        class="profile-form"
                        data-phone-form
                        data-send-url="{{ route('auth.phone.send-code') }}"
                        data-text-empty="{{ __('Введите номер телефона перед отправкой кода.') }}"
                        data-text-sending="{{ __('Отправляем...') }}"
                        data-text-success="{{ __('Код отправлен. Проверьте SMS.') }}"
                        data-text-error="{{ __('Произошла ошибка. Попробуйте позже.') }}"
                        data-cooldown-seconds="60"
                        data-text-cooldown="{{ __('Повторная отправка через :seconds с', ['seconds' => ':seconds']) }}"
                    >
                        @csrf

                        <div class="profile-form__group">
                            <label for="profile_phone" class="brand-form-label">{{ __('Номер телефона') }}</label>
                            <div class="profile-phone-field">
                                <input
                                    type="tel"
                                    id="profile_phone"
                                    name="phone"
                                    value="{{ old('phone', $user->phone) }}"
                                    class="brand-form-control"
                                    placeholder="+374 00 000 000"
                                >
                                <button type="button" class="btn-brand-outline profile-phone-send" data-phone-send>
                                    {{ __('Отправить код') }}
                                </button>
                            </div>
                            <p class="profile-form__hint" data-phone-status>
                                {{ __('Мы отправим 6-значный код в SMS. Первые 10 минут код будет активен.') }}
                            </p>
                            @if ($errors->phoneVerification->has('phone'))
                                <p class="profile-form__error">{{ $errors->phoneVerification->first('phone') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__group">
                            <label for="profile_phone_code" class="brand-form-label">{{ __('Код подтверждения') }}</label>
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
                            @if ($errors->phoneVerification->has('verification_code'))
                                <p class="profile-form__error">{{ $errors->phoneVerification->first('verification_code') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                {{ __('Подтвердить номер') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-page__grid profile-page__grid--stacked">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('Безопасность') }}</p>
                            <h3 class="profile-card__title">{{ __('Смена пароля') }}</h3>
                        </div>
                        @if (session('status') === 'password-updated')
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ __('Обновлено') }}
                            </span>
                        @endif
                    </div>

                    <form method="post" action="{{ route('password.update') }}" class="profile-form">
                        @csrf
                        @method('put')

                        <div class="profile-form__group">
                            <label for="current_password" class="brand-form-label">{{ __('Текущий пароль') }}</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="profile-form__error">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__grid">
                            <div>
                                <label for="new_password" class="brand-form-label">{{ __('Новый пароль') }}</label>
                                <input
                                    type="password"
                                    id="new_password"
                                    name="password"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                @if ($errors->updatePassword->has('password'))
                                    <p class="profile-form__error">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="brand-form-label">{{ __('Повторите пароль') }}</label>
                                <input
                                    type="password"
                                    id="new_password_confirmation"
                                    name="password_confirmation"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                @if ($errors->updatePassword->has('password_confirmation'))
                                    <p class="profile-form__error">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-outline">
                                {{ __('Сохранить пароль') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="brand-surface profile-card profile-card--danger">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('Опасная зона') }}</p>
                            <h3 class="profile-card__title">{{ __('Удаление аккаунта') }}</h3>
                        </div>
                    </div>

                    <p class="profile-card__text">
                        {{ __('После удаления аккаунта восстановить данные будет невозможно. Скачайте важную информацию заранее и подтвердите действие паролем.') }}
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" class="profile-form">
                        @csrf
                        @method('delete')

                        <div class="profile-form__group">
                            <label for="delete_password" class="brand-form-label">{{ __('Пароль для подтверждения') }}</label>
                            <input
                                type="password"
                                id="delete_password"
                                name="password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            @if ($errors->userDeletion->has('password'))
                                <p class="profile-form__error">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__actions">
                            <button
                                type="submit"
                                class="btn-brand-red btn-brand-full"
                                onclick="return confirm('{{ __('Вы уверены, что хотите удалить аккаунт?') }}')"
                            >
                                {{ __('Удалить аккаунт') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>

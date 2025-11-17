<x-guest-layout>
    <section class="auth-card__section">
        <header class="auth-card__header">
            <h2 class="auth-card__title">{{ __('Создать аккаунт') }}</h2>
            <p class="auth-card__subtitle">
                {{ __('Регистрируйтесь и публикуйте автомобили, запчасти и шины, следите за сообщениями и избранными объявлениями.') }}
            </p>
        </header>

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ __('Не удалось сохранить форму. Исправьте ошибки и попробуйте снова.') }}</span>
            </div>
        @endif

        <div class="auth-social">
            <a href="{{ route('auth.provider.redirect', 'google') }}" class="btn-social btn-social--google">
                <i class="fa-brands fa-google"></i>
                {{ __('Зарегистрироваться через Google') }}
            </a>
            <a href="{{ route('auth.provider.redirect', 'facebook') }}" class="btn-social btn-social--facebook">
                <i class="fa-brands fa-facebook-f"></i>
                {{ __('Зарегистрироваться через Facebook') }}
            </a>
        </div>

        <div class="auth-divider">
            <span>{{ __('или заполните форму') }}</span>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="auth-form__field">
                <label for="name" class="auth-form__label">{{ __('Имя') }}</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-user"></i></span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="{{ __('Как к вам обращаться') }}"
                        class="auth-input__control"
                    >
                </div>
                @error('name')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__field">
                <label for="email" class="auth-form__label">Email</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-envelope"></i></span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="auth-input__control"
                    >
                </div>
                @error('email')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__field">
                <label for="phone" class="auth-form__label">{{ __('Телефон') }}</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-phone"></i></span>
                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        autocomplete="tel"
                        placeholder="+374 77 123 456"
                        class="auth-input__control"
                    >
                </div>
                <p class="auth-form__hint mt-2">
                    {{ __('Указывайте армянский номер в формате +374 XX XXX XXX. Номер должен быть уникальным.') }}
                </p>
                @error('phone')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__field">
                <label for="password" class="auth-form__label">{{ __('Пароль') }}</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('Минимум 8 символов') }}"
                        class="auth-input__control"
                    >
                </div>
                @error('password')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__field">
                <label for="password_confirmation" class="auth-form__label">{{ __('Повторите пароль') }}</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('Ещё раз пароль') }}"
                        class="auth-input__control"
                    >
                </div>
                @error('password_confirmation')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-brand-gradient btn-brand-full auth-form__submit">
                {{ __('Зарегистрироваться') }}
            </button>
            <p class="auth-form__hint text-center mt-2">
                {{ __('Мы отправим письмо с подтверждением на указанный email. Проверьте почту, чтобы активировать аккаунт.') }}
            </p>
        </form>

        <p class="auth-card__switch">
            {{ __('Уже есть аккаунт?') }}
            <a href="{{ route('login') }}">{{ __('Войти') }}</a>
        </p>
    </section>

</x-guest-layout>

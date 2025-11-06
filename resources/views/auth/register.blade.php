<x-guest-layout>
    <section class="auth-card__section">
        <header class="auth-card__header">
            <h2 class="auth-card__title">Создать аккаунт</h2>
            <p class="auth-card__subtitle">
                Регистрируйтесь и публикуйте автомобили, запчасти и шины, следите за сообщениями и избранными объявлениями.
            </p>
        </header>

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Не удалось сохранить форму. Исправьте ошибки и попробуйте снова.</span>
            </div>
        @endif

        <div class="auth-social">
            <a href="{{ route('auth.provider.redirect', 'google') }}" class="btn-social btn-social--google">
                <i class="fa-brands fa-google"></i>
                Зарегистрироваться через Google
            </a>
            <a href="{{ route('auth.provider.redirect', 'facebook') }}" class="btn-social btn-social--facebook">
                <i class="fa-brands fa-facebook-f"></i>
                Зарегистрироваться через Facebook
            </a>
        </div>

        <div class="auth-divider">
            <span>или заполните форму</span>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="auth-form__field">
                <label for="name" class="auth-form__label">Имя</label>
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
                        placeholder="Как к вам обращаться"
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
                <label for="password" class="auth-form__label">Пароль</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Минимум 8 символов"
                        class="auth-input__control"
                    >
                </div>
                @error('password')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__field">
                <label for="password_confirmation" class="auth-form__label">Повторите пароль</label>
                <div class="auth-input">
                    <span class="auth-input__icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ещё раз пароль"
                        class="auth-input__control"
                    >
                </div>
                @error('password_confirmation')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-brand-gradient btn-brand-full auth-form__submit">
                Зарегистрироваться
            </button>
        </form>

        <p class="auth-card__switch">
            Уже есть аккаунт?
            <a href="{{ route('login') }}">Войти</a>
        </p>
    </section>
</x-guest-layout>

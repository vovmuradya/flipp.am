<x-guest-layout>
    <section class="auth-card__section">
        <header class="auth-card__header">
            <h2 class="auth-card__title">Войти в аккаунт</h2>
            <p class="auth-card__subtitle">
                Управляйте объявлениями, переписывайтесь с покупателями и отслеживайте отклики в личном кабинете idrom.am.
            </p>
        </header>

        @if (session('status'))
            <div class="auth-alert auth-alert--success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Проверьте введённые данные и попробуйте снова.</span>
            </div>
        @endif

        @error('provider')
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div class="auth-social">
            <a href="{{ route('auth.provider.redirect', 'google') }}" class="btn-social btn-social--google">
                <i class="fa-brands fa-google"></i>
                Войти через Google
            </a>
            <a href="{{ route('auth.provider.redirect', 'facebook') }}" class="btn-social btn-social--facebook">
                <i class="fa-brands fa-facebook-f"></i>
                Войти через Facebook
            </a>
        </div>

        <div class="auth-divider">
            <span>или</span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

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
                        autofocus
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
                        autocomplete="current-password"
                        placeholder="Введите пароль"
                        class="auth-input__control"
                    >
                </div>
                @error('password')
                    <span class="auth-form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-form__options">
                <label class="auth-checkbox">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span>Запомнить меня</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link">Забыли пароль?</a>
                @endif
            </div>

            <button type="submit" class="btn-brand-red btn-brand-full auth-form__submit">
                Войти
            </button>
        </form>

        <p class="auth-card__switch">
            Нет аккаунта?
            <a href="{{ route('register') }}">Зарегистрироваться</a>
        </p>
    </section>
</x-guest-layout>

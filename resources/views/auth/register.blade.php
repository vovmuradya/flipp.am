<x-guest-layout>
    <section class="auth-card__section">
        <header class="auth-card__header">
            <h2 class="auth-card__title">{{ __('Создать аккаунт') }}</h2>
            <p class="auth-card__subtitle">
                {{ __('Регистрируйтесь и публикуйте автомобили, запчасти и шины, следите за сообщениями и избранными объявлениями.') }}
            </p>
        </header>

        <div id="register-global-error" class="auth-alert auth-alert--error" style="display: {{ $errors->any() ? 'flex' : 'none' }};">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ __('Не удалось сохранить форму. Исправьте ошибки и попробуйте снова.') }}</span>
        </div>

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

        <form method="POST" action="{{ route('register') }}" class="auth-form" id="register-form" novalidate>
            @csrf

            <div class="auth-form__field">
                <label for="name" class="auth-form__label">{{ __('Имя') }}</label>
                <div class="auth-input{{ $errors->has('name') ? ' auth-input--error' : '' }}">
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
                <span class="auth-form__error" id="error-name" style="display: {{ $errors->has('name') ? 'block' : 'none' }};">@error('name'){{ $message }}@enderror</span>
            </div>

            <div class="auth-form__field">
                <label for="email" class="auth-form__label">Email</label>
                <div class="auth-input{{ $errors->has('email') ? ' auth-input--error' : '' }}">
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
                <span class="auth-form__error" id="error-email" style="display: {{ $errors->has('email') ? 'block' : 'none' }};">@error('email'){{ $message }}@enderror</span>
            </div>

            <div class="auth-form__field">
                <label for="phone" class="auth-form__label">{{ __('Телефон') }}</label>
                <div class="auth-input{{ $errors->has('phone') ? ' auth-input--error' : '' }}">
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
                <span class="auth-form__error" id="error-phone" style="display: {{ $errors->has('phone') ? 'block' : 'none' }};">@error('phone'){{ $message }}@enderror</span>
            </div>

            <div class="auth-form__field">
                <label for="password" class="auth-form__label">{{ __('Пароль') }}</label>
                <div class="auth-input{{ $errors->has('password') ? ' auth-input--error' : '' }}">
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
                <span class="auth-form__error" id="error-password" style="display: {{ $errors->has('password') ? 'block' : 'none' }};">@error('password'){{ $message }}@enderror</span>
            </div>

            <div class="auth-form__field">
                <label for="password_confirmation" class="auth-form__label">{{ __('Повторите пароль') }}</label>
                <div class="auth-input{{ $errors->has('password_confirmation') ? ' auth-input--error' : '' }}">
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
                <span class="auth-form__error" id="error-password_confirmation" style="display: {{ $errors->has('password_confirmation') ? 'block' : 'none' }};">@error('password_confirmation'){{ $message }}@enderror</span>
            </div>

            <button type="submit" class="btn-brand-gradient btn-brand-full auth-form__submit">
                {{ __('Зарегистрироваться') }}
            </button>
            <div id="register-server-message" class="auth-form__error text-center mt-2" style="display: none;"></div>
            <p class="auth-form__hint text-center mt-2">
                {{ __('Мы отправим письмо с подтверждением на указанный email. Проверьте почту, чтобы активировать аккаунт.') }}
            </p>
        </form>

        <p class="auth-card__switch">
            {{ __('Уже есть аккаунт?') }}
            <a href="{{ route('login') }}">{{ __('Войти') }}</a>
        </p>
    </section>

    <script>
        (() => {
            const form = document.getElementById('register-form');
            if (!form) return;

            const styleTag = document.createElement('style');
            styleTag.innerHTML = `
                .auth-input--error .auth-input__control { border-color: #ef4444; }
                .auth-form__error { color: #ef4444; }
            `;
            document.head.appendChild(styleTag);

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const submitBtn = form.querySelector('button[type="submit"]');
            const globalError = document.getElementById('register-global-error');
            const serverMessage = document.getElementById('register-server-message');
            const fields = ['name', 'email', 'phone', 'password', 'password_confirmation'];

            const showGlobalError = (show = true) => {
                if (!globalError) return;
                globalError.style.display = show ? 'flex' : 'none';
            };

            const clearErrors = () => {
                showGlobalError(false);
                if (serverMessage) {
                    serverMessage.textContent = '';
                    serverMessage.style.display = 'none';
                }
                fields.forEach((field) => {
                    const el = document.getElementById(`error-${field}`);
                    if (el) {
                        el.textContent = '';
                        el.style.display = 'none';
                    }
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        const wrapper = input.closest('.auth-input');
                        wrapper?.classList.remove('auth-input--error');
                    }
                });
            };

            const setFieldError = (field, messages) => {
                const el = document.getElementById(`error-${field}`);
                if (!el || !messages || messages.length === 0) return;
                el.textContent = messages[0];
                el.style.display = 'block';
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    const wrapper = input.closest('.auth-input');
                    wrapper?.classList.add('auth-input--error');
                }
            };

            const clientValidate = () => {
                const errors = {};
                const emailVal = (form.querySelector('[name="email"]')?.value || '').trim();
                const phoneVal = (form.querySelector('[name="phone"]')?.value || '').trim();
                const passVal = form.querySelector('[name="password"]')?.value || '';
                const passConf = form.querySelector('[name="password_confirmation"]')?.value || '';

                if (!emailVal.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
                    errors.email = ['{{ __('Укажите корректный Email') }}'];
                }
                if (!phoneVal.match(/^\+374\d{8}$/)) {
                    errors.phone = ['{{ __('Введите номер в формате +374 XX XXX XXX') }}'];
                }
                if (passVal.length < 8) {
                    errors.password = ['{{ __('Минимум 8 символов') }}'];
                }
                if (passVal !== passConf) {
                    errors.password_confirmation = ['{{ __('Пароли не совпадают') }}'];
                }
                return errors;
            };

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearErrors();

                const clientErrors = clientValidate();
                if (Object.keys(clientErrors).length > 0) {
                    Object.entries(clientErrors).forEach(([field, messages]) => setFieldError(field, messages));
                    showGlobalError(true);
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.dataset.originalText = submitBtn.textContent;
                    submitBtn.textContent = '{{ __('Регистрация...') }}';
                }

                const formData = new FormData(form);
                const payload = {};
                formData.forEach((value, key) => {
                    payload[key] = value;
                });

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    });

                    let data = {};
                    let text = '';
                    try { data = await response.clone().json(); } catch (_) {}
                    try { text = await response.text(); } catch (_) {}

                    if (response.ok && data?.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    if (response.status === 422 && data?.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            setFieldError(field, messages);
                        });
                        showGlobalError(true);
                    } else {
                        // Попробуем показать текст ошибки с бэка, если есть
                        if (text) {
                            const msg = text.slice(0, 200);
                            if (serverMessage) {
                                serverMessage.textContent = msg;
                                serverMessage.style.display = 'block';
                            } else {
                                globalError.querySelector('span').textContent = msg;
                            }
                        }
                        showGlobalError(true);
                    }
                } catch (err) {
                    showGlobalError(true);
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.originalText || submitBtn.textContent;
                    }
                }
            });
        })();
    </script>
</x-guest-layout>

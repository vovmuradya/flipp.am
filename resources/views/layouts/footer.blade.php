<footer class="idrom-footer">
    <div class="idrom-footer__main">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Бренд и описание -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="idrom-footer__brand">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="idrom-footer__logo" aria-hidden="true">
                                <img src="{{ asset('images/logo.png') }}" alt="idrom.am" class="idrom-footer__logo-img" loading="lazy">
                            </span>
                            <h3 class="idrom-footer__title mb-0">idrom.am</h3>
                        </div>
                        <p class="idrom-footer__subtitle">
                            {{ __('Лучшие авто предложения Армении и топовых международных аукционов. Быстро, безопасно, удобно.') }}
                        </p>
                        <div class="idrom-footer__social mt-4">
                            <a href="#" title="Telegram" aria-label="Telegram" class="idrom-footer__social-link">
                                <i class="fa-brands fa-telegram"></i>
                            </a>
                            <a href="#" title="Facebook" aria-label="Facebook" class="idrom-footer__social-link">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                            <a href="#" title="Instagram" aria-label="Instagram" class="idrom-footer__social-link">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="#" title="WhatsApp" aria-label="WhatsApp" class="idrom-footer__social-link">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Навигация -->
                <div class="col-6 col-md-6 col-lg-2">
                    <h4 class="idrom-footer__heading">{{ __('Категории') }}</h4>
                    <nav class="idrom-footer__nav" aria-label="{{ __('Категории') }}">
                        <a href="{{ route('home', ['only_regular' => 1]) }}" class="idrom-footer__link">
                            <i class="fa-solid fa-car me-2"></i>{{ __('Автомобили') }}
                        </a>
                        <a href="{{ route('home', ['only_auctions' => 1]) }}" class="idrom-footer__link">
                            <i class="fa-solid fa-gavel me-2"></i>{{ __('Аукционы') }}
                        </a>
                        <a href="{{ route('search.index') }}" class="idrom-footer__link">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>{{ __('Поиск') }}
                        </a>
                    </nav>
                </div>

                <div class="col-6 col-md-6 col-lg-2">
                    <h4 class="idrom-footer__heading">{{ __('Сервисы') }}</h4>
                    <nav class="idrom-footer__nav" aria-label="{{ __('Сервисы') }}">
                        <a href="{{ route('listings.create-choice') }}" class="idrom-footer__link">
                            <i class="fa-solid fa-plus me-2"></i>{{ __('Подать объявление') }}
                        </a>
                        @auth
                <a href="{{ route('dashboard.index') }}" class="idrom-footer__link">
                                <i class="fa-solid fa-gauge me-2"></i>{{ __('Панель управления') }}
                            </a>
                            <a href="{{ route('dashboard.my-listings') }}" class="idrom-footer__link">
                                <i class="fa-solid fa-list me-2"></i>{{ __('Мои объявления') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="idrom-footer__link">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>{{ __('Войти') }}
                            </a>
                            <a href="{{ route('register') }}" class="idrom-footer__link">
                                <i class="fa-solid fa-user-plus me-2"></i>{{ __('Регистрация') }}
                            </a>
                        @endauth
                    </nav>
                </div>

                <!-- Контакты -->
                <div class="col-12 col-md-6 col-lg-4">
                    <h4 class="idrom-footer__heading">{{ __('Контакты') }}</h4>
                    <ul class="idrom-footer__contacts">
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:idrom.am.info@gmail.com">idrom.am.info@gmail.com</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <a href="tel:+37477352465">+374 77 35 24 65</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>{{ __('Ереван, Армения') }}</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-clock"></i>
                            <span>{{ __('Пн-Вс: 09:00 - 21:00') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Нижняя часть футера -->
    <div class="idrom-footer__bottom">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 idrom-footer__copyright">
                        &copy; {{ date('Y') }} <strong>idrom.am</strong> — {{ __('Все права защищены') }}
                    </p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <nav class="idrom-footer__legal" aria-label="{{ __('Правовая информация') }}">
                        <a href="{{ url('/privacy') }}" class="idrom-footer__legal-link">
                            {{ __('Политика конфиденциальности') }}
                        </a>
                        <span class="idrom-footer__separator" aria-hidden="true">•</span>
                        <a href="{{ url('/terms') }}" class="idrom-footer__legal-link">
                            {{ __('Условия использования') }}
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</footer>

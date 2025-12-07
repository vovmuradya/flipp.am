<nav class="navbar navbar-expand-lg navbar-dark idrom-navbar">
    @php
        $mainNavLinks = [
            [
                'label' => __('Автомобили'),
                'href' => route('home', ['only_regular' => 1]),
                'active' => request()->routeIs('home') && request('only_regular'),
            ],
            [
                'label' => __('Автомобили из аукционов'),
                'href' => route('home', ['only_auctions' => 1]),
                'active' => request()->routeIs('home') && request('only_auctions'),
            ],
        ];

        $supportedLocales = config('app.supported_locales', []);
        $localeLabels = config('app.locale_labels', []);
        $localeOptions = collect($supportedLocales)
            ->mapWithKeys(fn ($code) => [
                $code => [
                    'short' => $localeLabels[$code]['short'] ?? strtoupper($code),
                    'label' => $localeLabels[$code]['label'] ?? strtoupper($code),
                ],
            ])
            ->all();
        $currentLocale = app()->getLocale();
        $favoriteCount = auth()->check() ? auth()->user()->favorites()->count() : 0;
    @endphp
    <div class="container-fluid align-items-center">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="brand-logo" role="presentation">
                <img src="{{ asset('images/logo.png') }}" alt="" class="brand-logo__img">
            </span>
            <span class="brand-text">idrom.am</span>
        </a>

        <div class="mobile-action-buttons d-lg-none">
            <div
                class="mobile-search-inline"
                x-data="{ open: false }"
                x-on:click.away="open = false"
                @keydown.escape.stop="open = false"
            >
                <form
                    action="{{ route('search.index') }}"
                    method="GET"
                    class="mobile-search-inline__form"
                    :class="{ 'is-open': open }"
                >
                    <div
                        class="mobile-search-inline__field"
                        :aria-hidden="(!open).toString()"
                    >
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            class="form-control nav-search__input"
                            placeholder="{{ __('Поиск по объявлениям…') }}"
                            x-ref="mobileSearchInput"
                            :tabindex="open ? 0 : -1"
                        >
                    </div>
                    <button
                        type="button"
                        class="icon-button mobile-icon-button"
                        aria-label="{{ __('Поиск') }}"
                        :aria-expanded="open.toString()"
                        @click.prevent="open = !open; if (open) { $nextTick(() => $refs.mobileSearchInput.focus()); }"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>

            <a
                href="{{ route('listings.create-choice') }}"
                class="mobile-icon-button mobile-add-button"
                aria-label="{{ __('Подать объявление') }}"
            >
                <i class="fa-solid fa-plus"></i>
            </a>

            <a
                href="{{ auth()->check() ? route('dashboard.favorites') : route('login') }}"
                class="mobile-icon-button mobile-favorite-button"
                aria-label="{{ __('Избранные') }}"
            >
                <i class="fa-solid fa-heart"></i>
                <span class="mobile-favorite-button__badge">
                    {{ $favoriteCount > 99 ? '99+' : $favoriteCount }}
                </span>
            </a>

            <button
                class="navbar-toggler mobile-icon-button border-0"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileMainMenu"
                aria-controls="mobileMainMenu"
                aria-expanded="false"
                aria-label="{{ __('Открыть меню') }}"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse flex-lg-grow-1" id="idromNavbar">
            <div class="nav-center d-flex flex-column flex-lg-row align-items-lg-center gap-3 w-100">
                <ul class="navbar-nav nav-center__links flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3 mb-0">
                    @foreach ($mainNavLinks as $link)
                        <li class="nav-item">
                            <a class="nav-link {{ $link['active'] ? 'active' : '' }}" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('search.index') }}" method="GET" class="nav-search d-none d-lg-block">
                    <div class="nav-search__wrapper">
                        <span class="nav-search__icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            class="form-control nav-search__input"
                            placeholder="{{ __('Поиск по объявлениям…') }}"
                        >
                        <button type="submit" class="nav-search__submit" aria-label="{{ __('Найти') }}">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

                <a href="{{ route('listings.create-choice') }}" class="btn btn-post nav-center__cta d-none d-lg-inline-flex">{{ __('Подать объявление') }}</a>

                <div class="action-toolbar d-none d-lg-flex align-items-center gap-2 ms-lg-auto">
                    @auth
                        <div class="dropdown-hover ms-3">
                            <span class="icon-button" title="{{ __('Профиль') }}"><i class="fa-solid fa-user"></i></span>
                            <div class="dropdown-menu shadow-sm">
                                <a class="dropdown-item" href="{{ route('dashboard.my-listings') }}">{{ __('Мои объявления') }}</a>
                                <a class="dropdown-item" href="{{ route('dashboard.my-auctions') }}">{{ __('Мои аукционы') }}</a>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Настройки') }}</a>
                                <a class="dropdown-item" href="{{ url('/support') }}">{{ __('Помощь') }}</a>
                                <div class="dropdown-item p-0">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-btn">{{ __('Выход') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('dashboard.messages') }}" class="icon-button" title="{{ __('Сообщения') }}"><i class="fa-solid fa-comment"></i></a>
                        <div class="dropdown-hover">
                            <span class="icon-button" title="{{ __('Сменить язык') }}"><i class="fa-solid fa-globe"></i></span>
                            <div class="dropdown-menu shadow-sm locale-dropdown-menu">
                                @foreach ($localeOptions as $code => $option)
                                    <form method="POST" action="{{ route('locale.update') }}" class="locale-dropdown-form">
                                        @csrf
                                        <input type="hidden" name="locale" value="{{ $code }}">
                                        <button type="submit" class="dropdown-item d-flex justify-content-between {{ $currentLocale === $code ? 'active' : '' }}">
                                            <span>{{ $option['label'] }}</span>
                                            @if($currentLocale === $code)
                                                <i class="fa-solid fa-check text-success ms-2"></i>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="icon-button ms-3" title="{{ __('Войти') }}"><i class="fa-solid fa-user"></i></a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end mobile-offcanvas" tabindex="-1" id="mobileMainMenu" aria-labelledby="mobileMainMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMainMenuLabel">
                @auth
                    {{ auth()->user()->name ?? __('Меню') }}
                @else
                    {{ __('Меню') }}
                @endauth
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Закрыть') }}"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-offcanvas__section">
                <button type="button" class="mobile-offcanvas__link mobile-offcanvas__link--primary" data-open-locale-modal>
                    <i class="fa-solid fa-globe"></i>
                    {{ __('Сменить язык') }}
                </button>
            </div>
            <div class="mobile-offcanvas__section">
                @foreach ($mainNavLinks as $link)
                    <a href="{{ $link['href'] }}" class="mobile-offcanvas__link {{ $link['active'] ? 'is-active' : '' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
            @auth
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('listings.create-choice') }}" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-plus-circle"></i>
                        {{ __('Подать объявление') }}
                    </a>
                    <a href="{{ route('dashboard.messages') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-comment-dots"></i>
                        {{ __('Сообщения') }}
                    </a>
                    <a href="{{ route('dashboard.favorites') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-heart"></i>
                        {{ __('Избранные') }}
                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('dashboard.my-listings') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-rectangle-list"></i>
                        {{ __('Мои объявления') }}
                    </a>
                    <a href="{{ route('dashboard.my-auctions') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gavel"></i>
                        {{ __('Мои аукционы') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gear"></i>
                        {{ __('Настройки') }}
                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <a href="{{ url('/support') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        {{ __('Помощь') }}
                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mobile-offcanvas__link mobile-offcanvas__link--danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            {{ __('Выход') }}
                        </button>
                    </form>
                </div>
            @else
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('login') }}" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        {{ __('Войти') }}
                    </a>
                    <a href="{{ route('register') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-user-plus"></i>
                        {{ __('Зарегистрироваться') }}
                    </a>
                    <a href="{{ url('/support') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        {{ __('Помощь') }}
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

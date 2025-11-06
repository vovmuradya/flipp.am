<nav class="navbar navbar-expand-lg navbar-dark idrom-navbar">
    @php
        $mainNavLinks = [
            [
                'label' => 'Автомобили',
                'href' => route('home', ['only_regular' => 1]),
                'active' => request()->routeIs('home') && request('only_regular'),
            ],
            [
                'label' => 'Автомобили из аукционов',
                'href' => route('home', ['only_auctions' => 1]),
                'active' => request()->routeIs('home') && request('only_auctions'),
            ],
            [
                'label' => 'Запчасти',
                'href' => '#',
                'active' => request()->routeIs('parts.*'),
            ],
            [
                'label' => 'Шины',
                'href' => '#',
                'active' => request()->routeIs('tires.*'),
            ],
        ];
    @endphp
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="brand-logo">
                <i class="fa-solid fa-car-side"></i>
            </span>
            <span class="brand-text">idrom.am</span>
        </a>

        <div class="mobile-action-buttons d-lg-none ms-auto">
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
                            placeholder="Поиск по объявлениям…"
                            x-ref="mobileSearchInput"
                            :tabindex="open ? 0 : -1"
                        >
                    </div>
                    <button
                        type="button"
                        class="icon-button mobile-icon-button"
                        aria-label="Поиск"
                        :aria-expanded="open.toString()"
                        @click.prevent="open = !open; if (open) { $nextTick(() => $refs.mobileSearchInput.focus()); }"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>

            <button
                class="icon-button mobile-icon-button"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileUserPanel"
                aria-controls="mobileUserPanel"
                aria-label="Меню пользователя"
            >
                <i class="fa-solid fa-user"></i>
            </button>

            <button
                class="navbar-toggler mobile-icon-button border-0"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileMainMenu"
                aria-controls="mobileMainMenu"
                aria-expanded="false"
                aria-label="Открыть меню"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse flex-lg-grow-1" id="idromNavbar">
            <div class="nav-center d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-center gap-3 w-100">
                <ul class="navbar-nav nav-center__links flex-column flex-lg-row align-items-lg-center justify-content-lg-center gap-2 gap-lg-3 mb-0">
                    @foreach ($mainNavLinks as $link)
                        <li class="nav-item">
                            <a class="nav-link {{ $link['active'] ? 'active' : '' }}" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('search.index') }}" method="GET" class="nav-search d-none d-lg-flex align-items-center">
                    <i class="fa-solid fa-magnifying-glass nav-search__icon"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        class="form-control nav-search__input"
                        placeholder="Поиск по объявлениям…"
                    >
                    <button type="submit" class="btn nav-search__btn">Найти</button>
                </form>
            </div>
        </div>

        <div class="action-toolbar d-none d-lg-flex align-items-center gap-2 ms-auto ms-lg-4">
            <a href="{{ route('listings.create-choice') }}" class="btn btn-post">Подать объявление</a>

            @auth
                <div class="dropdown-hover ms-3">
                    <span class="icon-button" title="Профиль"><i class="fa-solid fa-user"></i></span>
                    <div class="dropdown-menu shadow-sm">
                        <a class="dropdown-item" href="{{ route('dashboard.my-listings') }}">Мои объявления</a>
                        <a class="dropdown-item" href="{{ route('dashboard.my-auctions') }}">Мои аукционы</a>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">Настройки</a>
                        <a class="dropdown-item" href="{{ url('/support') }}">Помощь</a>
                        <div class="dropdown-item p-0">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-btn">Выход</button>
                            </form>
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard.messages') }}" class="icon-button" title="Сообщения"><i class="fa-solid fa-comment"></i></a>
                <a href="{{ route('dashboard.index') }}" class="icon-button" title="Уведомления"><i class="fa-solid fa-bell"></i></a>
            @else
                <a href="{{ route('login') }}" class="icon-button ms-3" title="Войти"><i class="fa-solid fa-user"></i></a>
            @endauth
        </div>
    </div>

    <div class="offcanvas offcanvas-end mobile-offcanvas" tabindex="-1" id="mobileMainMenu" aria-labelledby="mobileMainMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMainMenuLabel">Разделы</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-offcanvas__section">
                @foreach ($mainNavLinks as $link)
                    <a href="{{ $link['href'] }}" class="mobile-offcanvas__link {{ $link['active'] ? 'is-active' : '' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileUserPanel" aria-labelledby="mobileUserPanelLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileUserPanelLabel">@auth {{ auth()->user()->name ?? 'Профиль' }} @else Аккаунт @endauth</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body">
            @auth
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('listings.create-choice') }}" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-plus-circle"></i>
                        Подать объявление
                    </a>
                    <a href="{{ route('dashboard.messages') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-comment-dots"></i>
                        Сообщения
                    </a>
                    <a href="{{ route('dashboard.favorites') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-heart"></i>
                        Избранные
                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('dashboard.my-listings') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-rectangle-list"></i>
                        Мои объявления
                    </a>
                    <a href="{{ route('dashboard.my-auctions') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gavel"></i>
                        Мои аукционы
                    </a>
                    <a href="{{ route('profile.edit') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gear"></i>
                        Настройки
                    </a>
                    <a href="{{ url('/support') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        Помощь
                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mobile-offcanvas__link mobile-offcanvas__link--danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Выход
                        </button>
                    </form>
                </div>
            @else
                <div class="mobile-offcanvas__section">
                    <a href="{{ route('login') }}" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Войти
                    </a>
                    <a href="{{ route('register') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-user-plus"></i>
                        Зарегистрироваться
                    </a>
                    <a href="{{ url('/support') }}" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        Помощь
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

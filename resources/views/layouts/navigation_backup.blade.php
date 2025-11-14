<nav class="navbar navbar-expand-lg navbar-dark idrom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="brand-logo">
            </span>
            <span class="brand-text">idrom.am</span>
        </a>

        <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#idromNavbar" aria-controls="idromNavbar" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-lg-grow-1" id="idromNavbar">
            <div class="nav-center d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-center gap-3 w-100">
                <ul class="navbar-nav nav-center__links flex-column flex-lg-row align-items-lg-center justify-content-lg-center gap-2 gap-lg-3 mb-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Автомобили</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard.my-auctions') ? 'active' : '' }}" href="{{ route('dashboard.my-auctions') }}">Автомобили из аукционов</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="#">Запчасти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tires.*') ? 'active' : '' }}" href="#">Шины</a>
                    </li>
                </ul>

                <form action="{{ route('search.index') }}" method="GET" class="nav-search d-flex align-items-center">
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

        <div class="d-flex align-items-center gap-2 ms-auto ms-lg-4 action-toolbar">
            <a href="{{ route('listings.create') }}" class="btn btn-post">Подать объявление</a>
            <a href="{{ route('listings.create-from-auction') }}" class="btn btn-auction ms-2">Добавить из аукциона</a>

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
</nav>

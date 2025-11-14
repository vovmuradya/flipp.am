<nav class="navbar navbar-expand-lg navbar-dark idrom-navbar">
    <?php
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
    ?>
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
            <span class="brand-logo" role="presentation">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="" class="brand-logo__img">
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
                    action="<?php echo e(route('search.index')); ?>"
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
                            value="<?php echo e(request('q')); ?>"
                            class="form-control nav-search__input"
                            placeholder="<?php echo e(__('Поиск по объявлениям…')); ?>"
                            x-ref="mobileSearchInput"
                            :tabindex="open ? 0 : -1"
                        >
                    </div>
                    <button
                        type="button"
                        class="icon-button mobile-icon-button"
                        aria-label="<?php echo e(__('Поиск')); ?>"
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
                aria-label="<?php echo e(__('Меню пользователя')); ?>"
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
                aria-label="<?php echo e(__('Открыть меню')); ?>"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse flex-lg-grow-1" id="idromNavbar">
            <div class="nav-center d-flex flex-column flex-lg-row align-items-lg-center gap-3 w-100">
                <ul class="navbar-nav nav-center__links flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3 mb-0">
                    <?php $__currentLoopData = $mainNavLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e($link['active'] ? 'active' : ''); ?>" href="<?php echo e($link['href']); ?>"><?php echo e($link['label']); ?></a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <form action="<?php echo e(route('search.index')); ?>" method="GET" class="nav-search d-none d-lg-block">
                    <div class="nav-search__wrapper">
                        <span class="nav-search__icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            type="search"
                            name="q"
                            value="<?php echo e(request('q')); ?>"
                            class="form-control nav-search__input"
                            placeholder="<?php echo e(__('Поиск по объявлениям…')); ?>"
                        >
                        <button type="submit" class="nav-search__submit" aria-label="<?php echo e(__('Найти')); ?>">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

                <a href="<?php echo e(route('listings.create-choice')); ?>" class="btn btn-post nav-center__cta d-none d-lg-inline-flex"><?php echo e(__('Подать объявление')); ?></a>

                <div class="action-toolbar d-none d-lg-flex align-items-center gap-2 ms-lg-auto">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="dropdown-hover ms-3">
                            <span class="icon-button" title="<?php echo e(__('Профиль')); ?>"><i class="fa-solid fa-user"></i></span>
                            <div class="dropdown-menu shadow-sm">
                                <a class="dropdown-item" href="<?php echo e(route('dashboard.my-listings')); ?>"><?php echo e(__('Мои объявления')); ?></a>
                                <a class="dropdown-item" href="<?php echo e(route('dashboard.my-auctions')); ?>"><?php echo e(__('Мои аукционы')); ?></a>
                                <a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><?php echo e(__('Настройки')); ?></a>
                                <a class="dropdown-item" href="<?php echo e(url('/support')); ?>"><?php echo e(__('Помощь')); ?></a>
                                <div class="dropdown-item p-0">
                                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dropdown-btn"><?php echo e(__('Выход')); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo e(route('dashboard.messages')); ?>" class="icon-button" title="<?php echo e(__('Сообщения')); ?>"><i class="fa-solid fa-comment"></i></a>
                        <div class="dropdown-hover">
                            <span class="icon-button" title="<?php echo e(__('Сменить язык')); ?>"><i class="fa-solid fa-globe"></i></span>
                            <div class="dropdown-menu shadow-sm locale-dropdown-menu">
                                <?php $__currentLoopData = $localeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <form method="POST" action="<?php echo e(route('locale.update')); ?>" class="locale-dropdown-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="locale" value="<?php echo e($code); ?>">
                                        <button type="submit" class="dropdown-item d-flex justify-content-between <?php echo e($currentLocale === $code ? 'active' : ''); ?>">
                                            <span><?php echo e($option['label']); ?></span>
                                            <?php if($currentLocale === $code): ?>
                                                <i class="fa-solid fa-check text-success ms-2"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="icon-button ms-3" title="<?php echo e(__('Войти')); ?>"><i class="fa-solid fa-user"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end mobile-offcanvas" tabindex="-1" id="mobileMainMenu" aria-labelledby="mobileMainMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileMainMenuLabel"><?php echo e(__('Разделы')); ?></h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php echo e(__('Закрыть')); ?>"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-offcanvas__section">
                <button type="button" class="mobile-offcanvas__link mobile-offcanvas__link--primary" data-open-locale-modal>
                    <i class="fa-solid fa-globe"></i>
                    <?php echo e(__('Сменить язык')); ?>

                </button>
            </div>
            <div class="mobile-offcanvas__section">
                <?php $__currentLoopData = $mainNavLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($link['href']); ?>" class="mobile-offcanvas__link <?php echo e($link['active'] ? 'is-active' : ''); ?>">
                        <?php echo e($link['label']); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileUserPanel" aria-labelledby="mobileUserPanelLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileUserPanelLabel">
                <?php if(auth()->guard()->check()): ?>
                    <?php echo e(auth()->user()->name ?? __('Профиль')); ?>

                <?php else: ?>
                    <?php echo e(__('Аккаунт')); ?>

                <?php endif; ?>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php echo e(__('Закрыть')); ?>"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-offcanvas__section">
                <button type="button" class="mobile-offcanvas__link" data-open-locale-modal>
                    <i class="fa-solid fa-globe"></i>
                    <?php echo e(__('Сменить язык')); ?>

                </button>
            </div>
            <?php if(auth()->guard()->check()): ?>
                <div class="mobile-offcanvas__section">
                    <a href="<?php echo e(route('listings.create-choice')); ?>" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-plus-circle"></i>
                        <?php echo e(__('Подать объявление')); ?>

                    </a>
                    <a href="<?php echo e(route('dashboard.messages')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-comment-dots"></i>
                        <?php echo e(__('Сообщения')); ?>

                    </a>
                    <a href="<?php echo e(route('dashboard.favorites')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-heart"></i>
                        <?php echo e(__('Избранные')); ?>

                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <a href="<?php echo e(route('dashboard.my-listings')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-rectangle-list"></i>
                        <?php echo e(__('Мои объявления')); ?>

                    </a>
                    <a href="<?php echo e(route('dashboard.my-auctions')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gavel"></i>
                        <?php echo e(__('Мои аукционы')); ?>

                    </a>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-gear"></i>
                        <?php echo e(__('Настройки')); ?>

                    </a>
                    <a href="<?php echo e(url('/support')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        <?php echo e(__('Помощь')); ?>

                    </a>
                </div>
                <div class="mobile-offcanvas__section">
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="mobile-offcanvas__link mobile-offcanvas__link--danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <?php echo e(__('Выход')); ?>

                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="mobile-offcanvas__section">
                    <a href="<?php echo e(route('login')); ?>" class="mobile-offcanvas__link mobile-offcanvas__link--primary">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <?php echo e(__('Войти')); ?>

                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-user-plus"></i>
                        <?php echo e(__('Зарегистрироваться')); ?>

                    </a>
                    <a href="<?php echo e(url('/support')); ?>" class="mobile-offcanvas__link">
                        <i class="fa-solid fa-circle-question"></i>
                        <?php echo e(__('Помощь')); ?>

                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/layouts/navigation.blade.php ENDPATH**/ ?>
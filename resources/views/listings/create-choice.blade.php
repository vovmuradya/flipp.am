<x-app-layout>
    <section class="brand-section create-choice">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">Какое объявление вы хотите разместить?</h2>
                <p class="brand-section__subtitle">
                    Выберите подходящий формат и заполните форму, чтобы ваше объявление появилось на idrom.am.
                </p>
            </div>

            @php
                $canCreateAuction = auth()->user()?->isDealer() || auth()->user()?->isAdmin();
            @endphp

            <div class="create-choice__grid">
                <a href="{{ route('listings.create') }}" class="create-choice__card">
                    <div class="create-choice__icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3 class="create-choice__card-title">Обычное объявление</h3>
                    <p class="create-choice__card-text">
                        Подходит для частных продавцов и автосалонов. Добавьте автомобиль, запчасти или шины с подробным описанием и фото.
                    </p>
                    <span class="create-choice__cta">
                        Перейти к форме
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a
                    @class([
                        'create-choice__card',
                        'create-choice__card--auction',
                        'create-choice__card--disabled' => !$canCreateAuction,
                    ])
                    href="{{ $canCreateAuction ? route('listings.create-from-auction') : '#' }}"
                    @unless($canCreateAuction)
                        aria-disabled="true"
                        role="button"
                    @endunless
                >
                    <div class="create-choice__icon">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                    <h3 class="create-choice__card-title">Объявление из аукциона</h3>
                    <p class="create-choice__card-text">
                        Импортируйте данные по лоту Copart/IAAI и быстро создайте объявление с уже заполненными характеристиками.
                    </p>
                    <span class="create-choice__cta">
                        {{ $canCreateAuction ? 'Перейти к импорту' : 'Доступно для дилеров' }}
                        @if($canCreateAuction)
                            <i class="fa-solid fa-arrow-right"></i>
                        @else
                            <i class="fa-solid fa-lock"></i>
                        @endif
                    </span>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>

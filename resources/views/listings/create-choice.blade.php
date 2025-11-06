<x-app-layout>
    <section class="brand-section create-choice">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">Какое объявление вы хотите разместить?</h2>
                <p class="brand-section__subtitle">
                    Выберите подходящий формат и заполните форму, чтобы ваше объявление появилось на idrom.am.
                </p>
            </div>
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

                <a href="{{ route('listings.create-from-auction') }}" class="create-choice__card create-choice__card--auction">
                    <div class="create-choice__icon">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                    <h3 class="create-choice__card-title">Объявление из аукциона</h3>
                    <p class="create-choice__card-text">
                        Импортируйте данные по лоту Copart и быстро создайте объявление с уже заполненными характеристиками.
                    </p>
                    <span class="create-choice__cta">
                        Перейти к импорту
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>

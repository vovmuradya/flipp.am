@php
    $supportedLocales = config('app.supported_locales', []);
    $localeLabels = config('app.locale_labels', []);
    $localeOptions = collect($supportedLocales)->mapWithKeys(function ($code) use ($localeLabels) {
        return [
            $code => [
                'label' => $localeLabels[$code]['label'] ?? strtoupper($code),
                'description' => match ($code) {
                    'hy' => __('Армянская версия'),
                    'ru' => __('Русская версия'),
                    'en' => __('English version'),
                    default => strtoupper($code),
                },
            ],
        ];
    });
@endphp

<div id="localeModal" class="locale-modal" aria-hidden="true">
    <div class="locale-modal__backdrop" data-locale-modal-close></div>
    <div class="locale-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="localeModalTitle">
        <button type="button" class="locale-modal__close" data-locale-modal-close aria-label="{{ __('Закрыть') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="locale-modal__header">
            <h2 id="localeModalTitle">{{ __('Выберите язык') }}</h2>
            <p>{{ __('Продолжайте на удобном языке — мы запомним ваш выбор.') }}</p>
        </div>
        <div class="locale-modal__body">
            @foreach($localeOptions as $code => $option)
                <form method="POST" action="{{ route('locale.update') }}" class="locale-modal__form">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit" class="locale-modal__option" data-locale-option="{{ $code }}">
                        <span class="locale-modal__option-title">{{ $option['label'] }}</span>
                        <span class="locale-modal__option-desc">{{ $option['description'] }}</span>
                        @if(app()->getLocale() === $code)
                            <span class="locale-modal__option-badge">{{ __('Текущий') }}</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
        <div class="locale-modal__footer">
            <button type="button" class="btn btn-outline-secondary w-100" data-locale-modal-close>
                {{ __('Продолжить позже') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('localeModal');
            if (!modal) {
                return;
            }

            const openModal = () => {
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('is-visible');
                document.body.classList.add('locale-modal-open');
            };

            const closeModal = () => {
                modal.setAttribute('aria-hidden', 'true');
                modal.classList.remove('is-visible');
                document.body.classList.remove('locale-modal-open');
            };

            const firstVisit = !localStorage.getItem('appLocaleSelected');
            if (firstVisit) {
                openModal();
            }

            document.querySelectorAll('[data-open-locale-modal]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    openModal();
                });
            });

            modal.querySelectorAll('[data-locale-modal-close]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    closeModal();
                    localStorage.setItem('appLocaleSelected', '1');
                });
            });

            modal.querySelectorAll('.locale-modal__form').forEach((form) => {
                form.addEventListener('submit', () => {
                    localStorage.setItem('appLocaleSelected', '1');
                });
            });
        });
    </script>
@endpush

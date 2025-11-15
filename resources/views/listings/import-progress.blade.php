<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Импортируем автомобиль с аукциона') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Мы занимаемся обработкой лота. Это занимает до нескольких секунд — можете оставаться на странице, мы перенаправим вас автоматически.') }}
                </p>
            </div>

            <div class="brand-surface">
                <div class="d-flex flex-column gap-4 align-items-center py-5" id="import-progress-container">
                    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                        <span class="visually-hidden">{{ __('Загрузка...') }}</span>
                    </div>
                    <div class="text-center">
                        <p class="mb-1 fw-semibold">{{ __('Парсим данные Copart') }}</p>
                        <p class="text-muted mb-0">{{ __('Можно оставить вкладку открытой — мы автоматически перейдём к заполнению объявления.') }}</p>
                    </div>
                </div>
                <div class="alert alert-danger mt-4 d-none" id="import-error" role="alert"></div>
                <div class="text-center mt-3">
                    <a href="{{ route('listings.create-from-auction') }}" class="btn btn-link text-decoration-none">{{ __('Вернуться назад') }}</a>
                </div>
            </div>
        </div>
    </section>

    <form id="consume-import-form" action="{{ route('listings.import-consume', $import) }}" method="POST" class="d-none">
        @csrf
    </form>

    <script>
        (function () {
            const statusUrl = @json(route('listings.import-status', $import));
            const consumeUrl = document.getElementById('consume-import-form').action;
            const errorBox = document.getElementById('import-error');
            const spinnerContainer = document.getElementById('import-progress-container');

            const poll = () => {
                fetch(statusUrl, {headers: {'Accept': 'application/json'}})
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            fetch(consumeUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                            })
                                .then(response => response.json())
                                .then(payload => {
                                    if (payload.redirect) {
                                        window.location.href = payload.redirect;
                                    } else {
                                        showError('{{ __('Не удалось подготовить форму объявления.') }}');
                                    }
                                })
                                .catch(() => showError('{{ __('Не удалось подготовить форму объявления.') }}'));
                            return;
                        }

                        if (data.status === 'failed') {
                            showError(data.error || '{{ __('Не удалось импортировать данные. Попробуйте позже.') }}');
                            return;
                        }

                        setTimeout(poll, 2000);
                    })
                    .catch(() => {
                        setTimeout(poll, 4000);
                    });
            };

            const showError = (message) => {
                if (spinnerContainer) {
                    spinnerContainer.classList.add('d-none');
                }

                errorBox.textContent = message;
                errorBox.classList.remove('d-none');
            };

            poll();
        })();
    </script>
</x-app-layout>

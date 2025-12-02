<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('Импорт автомобиля с аукциона') }}</h2>
                <p class="brand-section__subtitle">
                    {{ __('Вставьте ссылку на лот Copart — мы автоматически подготовим черновик объявления и поможем ускорить публикацию.') }}
                </p>
            </div>

            <div class="brand-surface px-4 px-lg-5 py-4">
                <div class="row g-5 align-items-start">
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <span class="badge rounded-pill text-bg-light text-uppercase fw-semibold">{{ __('Шаг 1 из 2') }}</span>
                                <h3 class="h4 fw-semibold mt-2 mb-0">{{ __('Укажите ссылку на аукционный лот') }}</h3>
                                <p class="text-muted mb-0">
                                    {{ __('После отправки мы подтянем характеристики, фото и стоимость — останется только проверить данные и опубликовать.') }}
                                </p>
                            </div>

                            <form id="auction-import-form" method="POST" action="{{ route('listings.import-auction') }}" class="d-flex flex-column gap-3" novalidate>
                                @csrf
                                <div class="d-flex flex-column gap-2">
                                    <label for="auction-url" class="form-label fw-semibold mb-0">{{ __('Ссылка на лот с аукциона') }}</label>
                                    <input
                                        type="url"
                                        id="auction-url"
                                    name="auction_url"
                                    class="form-control form-control-lg"
                                    placeholder="https://www.copart.com/lot/..."
                                    value="{{ old('auction_url') }}"
                                    required
                                >
                                <div class="form-text">{{ __('Поддерживаемая площадка:') }} <span class="fw-semibold">Copart.com</span></div>
                            </div>

                                <div id="auction-url-client-error" class="alert alert-danger mb-0 d-none" role="alert"></div>

                                @if ($errors->has('auction_url'))
                                    <div class="alert alert-danger mb-0">
                                        {{ $errors->first('auction_url') }}
                                    </div>
                                @endif

                                @if (session('auction_error'))
                                    <div class="alert alert-warning mb-0">
                                        {{ session('auction_error') }}
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-3 pt-1">
                                    <button id="auction-import-submit" type="submit" class="btn btn-brand-gradient btn-lg px-4">{{ __('Импортировать') }}</button>
                                    <a href="{{ route('home') }}" class="btn btn-brand-outline btn-lg px-4">{{ __('Отмена') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100 d-flex flex-column gap-4" style="background: rgba(17,24,39,0.04); border: 1px solid rgba(17,24,39,0.08);">
                            <div>
                                <h4 class="h5 fw-semibold mb-3">{{ __('Что произойдет дальше?') }}</h4>
                                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">1</span>
                                        <div>
                                            <span class="fw-semibold d-block">{{ __('Мы парсим лот за несколько секунд') }}</span>
                                            <span class="text-muted">{{ __('Загружаем характеристики, историю пробега и фотографии, если они доступны на площадке.') }}</span>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">2</span>
                                        <div>
                                            <span class="fw-semibold d-block">{{ __('Проверьте и дополните данные') }}</span>
                                            <span class="text-muted">{{ __('Система перенаправит на форму объявления, где можно скорректировать описание, цену и контакты.') }}</span>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-3">
                                        <span class="badge rounded-circle text-bg-danger d-inline-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">3</span>
                                        <div>
                                            <span class="fw-semibold d-block">{{ __('Публикуйте в один клик') }}</span>
                                            <span class="text-muted">{{ __('После проверки сохраните объявление в черновики или сразу опубликуйте на сайте.') }}</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-3 rounded-4" style="background: rgba(244,140,37,0.12); border: 1px solid rgba(244,140,37,0.2);">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="fs-4">💡</span>
                                    <div>
                                        <p class="mb-1 fw-semibold text-dark">{{ __('Лот ещё не опубликован?') }}</p>
                                        <p class="mb-0 text-muted">{{ __('Можно импортировать черновик и вернуться к заполнению позже — он сохранится в разделе «Мои объявления».') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="auction-loading-modal" class="auction-loading-modal d-none" role="dialog" aria-live="polite" aria-label="{{ __('Импорт автомобиля с аукциона') }}">
        <div class="auction-loading-card">
            <div class="auction-loading-spinner" aria-hidden="true"></div>
            <h3 class="auction-loading-title">{{ __('auction_loading_title') }}</h3>
            <p class="auction-loading-subtitle">{{ __('auction_loading_subtitle') }}</p>

            <div class="auction-loading-ad">
                <p class="auction-loading-ad-eyebrow">{{ __('auction_loading_ad_label') }}</p>
                <p class="auction-loading-ad-title">{{ __('auction_loading_ad_title') }}</p>
                <p class="auction-loading-ad-text">
                    {!! __('auction_loading_ad_text', [
                        'email' => sprintf(
                            '<a href="mailto:%1$s" class="fw-semibold text-decoration-none">%1$s</a>',
                            __('site_demo_notice_email')
                        ),
                    ]) !!}
                </p>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .auction-loading-modal {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.65);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1100;
                padding: 1.5rem;
            }

            .auction-loading-modal.d-none {
                display: none;
            }

            .auction-loading-card {
                background: #fff;
                border-radius: 1.5rem;
                padding: 2.75rem 2.25rem;
                max-width: 520px;
                width: 100%;
                text-align: center;
                box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
            }

            .auction-loading-spinner {
                width: 64px;
                height: 64px;
                margin: 0 auto 1.5rem;
                border-radius: 50%;
                border: 6px solid #fef3c7;
                border-top-color: #f97316;
                animation: auction-spin 1s linear infinite;
            }

            .auction-loading-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .auction-loading-subtitle {
                color: #475569;
                margin-bottom: 2rem;
            }

            .auction-loading-ad {
                border-radius: 1rem;
                padding: 1.5rem;
                background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(249, 115, 22, 0.25));
                border: 1px solid rgba(249, 115, 22, 0.35);
            }

            .auction-loading-ad-eyebrow {
                text-transform: uppercase;
                letter-spacing: 0.12em;
                font-size: 0.75rem;
                color: #b45309;
                margin-bottom: 0.35rem;
            }

            .auction-loading-ad-title {
                font-weight: 600;
                font-size: 1.1rem;
                margin-bottom: 0.25rem;
            }

            .auction-loading-ad-text {
                margin: 0;
                color: #7c2d12;
            }

            @keyframes auction-spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('auction-import-form');
                const urlInput = document.getElementById('auction-url');
                const clientError = document.getElementById('auction-url-client-error');
                const loadingModal = document.getElementById('auction-loading-modal');
                const submitButton = document.getElementById('auction-import-submit');
                if (!form || !urlInput || !clientError) {
                    return;
                }

                const allowedHosts = [
                    'copart.com'
                ];
                const messages = {
                    invalidUrl: @json(__('Некорректный адрес ссылки. Проверьте формат URL и попробуйте снова.')),
                    unsupportedHost: @json(__('Поддерживаются только ссылки с аукциона Copart.')),
                };

                form.addEventListener('submit', (event) => {
                    clientError.classList.add('d-none');
                    clientError.textContent = '';

                    const rawUrl = urlInput.value.trim();
                    if (!rawUrl) {
                        return;
                    }

                    let parsedHost;
                    try {
                        parsedHost = new URL(rawUrl).hostname.toLowerCase();
                    } catch (error) {
                        event.preventDefault();
                        clientError.textContent = messages.invalidUrl;
                        clientError.classList.remove('d-none');
                        return;
                    }

                    const isAllowed = allowedHosts.some((domain) => {
                        return parsedHost === domain || parsedHost.endsWith(`.${domain}`);
                    });

                    if (!isAllowed) {
                        event.preventDefault();
                        clientError.textContent = messages.unsupportedHost;
                        clientError.classList.remove('d-none');
                        return;
                    }

                    loadingModal?.classList.remove('d-none');
                    submitButton?.setAttribute('disabled', 'disabled');
                    submitButton?.classList.add('disabled');
                });
            });
        </script>
    @endpush
</x-app-layout>

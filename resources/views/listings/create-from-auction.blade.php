<x-app-layout>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Добавить автомобиль с аукциона</h1>

        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Вставьте ссылку на автомобиль с поддерживаемого аукциона (Copart, IAAI).
                Система автоматически извлечет основные данные об автомобиле.
            </p>
        </div>

        <!-- Форма для ввода URL -->
        <div id="url-form" class="space-y-4">
            <div>
                <label for="auction-url" class="block text-sm font-medium text-gray-700 mb-2">
                    Ссылка на лот с аукциона
                </label>
                <input
                    type="url"
                    id="auction-url"
                    name="url"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="https://www.copart.com/lot/..."
                    required
                >
                <p class="mt-2 text-sm text-gray-500">
                    Поддерживаемые сайты: Copart.com, IAAI.com
                </p>
            </div>

            <!-- Сообщения об ошибках -->
            <div id="error-message" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <p id="error-text"></p>
            </div>

            <!-- Кнопки -->
            <div class="flex gap-4">
                <button
                    type="button"
                    id="fetch-button"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span id="button-text">Далее</span>
                    <span id="button-loader" class="hidden">
                        <svg class="animate-spin inline-block w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Загрузка...
                    </span>
                </button>
                <a href="{{ route('listings.create') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Отмена
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('auction-url');
    const fetchButton = document.getElementById('fetch-button');
    const buttonText = document.getElementById('button-text');
    const buttonLoader = document.getElementById('button-loader');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');

    fetchButton.addEventListener('click', async function() {
        const url = urlInput.value.trim();

        if (!url) {
            showError('Пожалуйста, введите ссылку на аукцион');
            return;
        }

        hideError();
        fetchButton.disabled = true;
        buttonText.classList.add('hidden');
        buttonLoader.classList.remove('hidden');

        try {
            const response = await fetch('/api/v1/dealer/listings/fetch-from-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ url })
            });

            const data = await response.json();

            if (data.success || data.fallback) {
                // ✅ ИСПРАВЛЕНО: Сохраняем в Laravel session через форму
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("listings.save-auction-data") }}'; // Создадим новый маршрут

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrfInput);

                const dataInput = document.createElement('input');
                dataInput.type = 'hidden';
                dataInput.name = 'auction_data';
                dataInput.value = JSON.stringify(data.data);
                form.appendChild(dataInput);

                document.body.appendChild(form);
                form.submit();
            } else {
                showError(data.message || 'Произошла ошибка');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showError('Произошла ошибка при обработке запроса');
        } finally {
            fetchButton.disabled = false;
            buttonText.classList.remove('hidden');
            buttonLoader.classList.add('hidden');
        }
    });


    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }

    // Enter для отправки
    urlInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            fetchButton.click();
        }
    });
});
</script>
@endpush
</x-app-layout>


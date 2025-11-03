<x-app-layout>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Добавить автомобиль с аукциона</h1>

        <div id="result-area" class="mb-6">
            <p class="text-gray-600 mb-4">Вставьте ссылку на автомобиль с поддерживаемого аукциона (Copart, IAAI). Система автоматически извлечет основные данные об автомобиле.</p>
        </div>

        <div id="url-form" class="space-y-4">
            <div>
                <label for="auction-url" class="block text-sm font-medium text-gray-700 mb-2">Ссылка на лот с аукциона</label>
                <input type="url" id="auction-url" name="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="https://www.copart.com/lot/..." required>
                <p class="mt-2 text-sm text-gray-500">Поддерживаемые сайты: Copart.com, IAAI.com</p>
            </div>

            <div id="error-message" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <p id="error-text"></p>
            </div>

            <div class="flex gap-4">
                <button id="fetch-button" type="button" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Далее</button>
                <a href="{{ route('listings.create') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Отмена</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('auction-url');
    const fetchButton = document.getElementById('fetch-button');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');

    // Безопасный JSON-стринг URL для формы
    const saveAuctionDataUrl = @json(route('listings.save-auction-data'));

    fetchButton.addEventListener('click', async function() {
        const url = urlInput.value.trim();
        if (!url) { showError('Пожалуйста, введите ссылку на аукцион'); return; }

        hideError();
        fetchButton.disabled = true;
        try {
            const resp = await fetch(@json(route('api.auction.fetch')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin',
                body: JSON.stringify({ url })
            });

            if (!resp.ok) {
                const txt = await resp.text();
                showError('Сервер вернул ошибку: ' + resp.status + ' ' + txt);
                return;
            }

            const data = await resp.json();
            if (data.success || data.fallback) {
                // POST to saveAuctionData route to store in session
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = saveAuctionDataUrl;

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
                showError(data.message || 'Не удалось получить данные с аукциона');
            }

        } catch (e) {
            console.error(e);
            showError('Ошибка при запросе к серверу');
        } finally {
            fetchButton.disabled = false;
        }
    });

    function showError(msg) { errorText.textContent = msg; errorMessage.classList.remove('hidden'); }
    function hideError() { errorMessage.classList.add('hidden'); }
});
</script>
@endpush
</x-app-layout>

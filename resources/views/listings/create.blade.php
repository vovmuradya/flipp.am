<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Новое объявление</h2>

                    <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" id="listingForm">
                        @csrf
                        <div class="space-y-6">

                            {{-- ОСНОВНАЯ ИНФОРМАЦИЯ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Основная информация</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="title" class="block font-medium text-sm text-gray-700">Заголовок</label>
                                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                               class="block mt-1 w-full border-gray-300 rounded-md shadow-sm @error('title') border-red-500 @enderror">
                                        @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="category_id" class="block font-medium text-sm text-gray-700">Категория <span class="text-red-500">*</span></label>
                                        <select name="category_id" id="category_id" required
                                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm @error('category_id') border-red-500 @enderror">
                                            <option value="">Выберите категорию</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="description" class="block font-medium text-sm text-gray-700">Описание</label>
                                        <textarea name="description" id="description" rows="5" required
                                                  class="block mt-1 w-full border-gray-300 rounded-md shadow-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ХАРАКТЕРИСТИКИ (ДИНАМИЧЕСКИЙ БЛОК) --}}
                            <div id="custom-fields-container" class="p-6 border rounded-lg bg-gray-50 space-y-4" style="display: none;">
                                <h3 class="text-lg font-semibold mb-4">Характеристики</h3>
                                <div id="fields-wrapper"></div>
                            </div>

                            {{-- ЦЕНА И МЕСТОПОЛОЖЕНИЕ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Цена и местоположение</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="block font-medium text-sm text-gray-700">Цена (USD)</label>
                                        <input type="number" name="price" id="price" value="{{ old('price') }}" required step="0.01"
                                               class="block mt-1 w-full border-gray-300 rounded-md shadow-sm @error('price') border-red-500 @enderror">
                                        @error('price')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="region_id" class="block font-medium text-sm text-gray-700">Регион</label>
                                        <select name="region_id" id="region_id" required
                                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm @error('region_id') border-red-500 @enderror">
                                            <option value="">Выберите регион</option>
                                            @foreach($regions as $region)
                                                <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>
                                                    {{ $region->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('region_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ЗАГРУЗЧИК ИЗОБРАЖЕНИЙ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Изображения</h3>
                                <div>
                                    <label for="images" class="block font-medium text-sm text-gray-700">Загрузите фото (макс. 6)</label>
                                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                                           class="block mt-1 w-full">
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-4">
                            <a href="{{ route('dashboard.my-listings') }}" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                                Отмена
                            </a>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                Опубликовать
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            const fieldsContainer = document.getElementById('custom-fields-container');
            const fieldsWrapper = document.getElementById('fields-wrapper');
            const apiBase = 'http://localhost'; // Убедитесь, что URL правильный
            const allBrands = @json($brands->map(fn($brand) => ['value' => $brand->id, 'label' => $brand->name_ru]));
            // --- Загрузка полей при смене категории ---
            categorySelect.addEventListener('change', loadCustomFields);
            if (categorySelect.value) {
                loadCustomFields();
            }

            function loadCustomFields() {
                const categoryId = categorySelect.value;
                if (!categoryId) {
                    fieldsContainer.style.display = 'none';
                    fieldsWrapper.innerHTML = '';
                    return;
                }

                fieldsContainer.style.display = 'block';
                fieldsWrapper.innerHTML = '<p class="text-gray-500">Загрузка характеристик...</p>';

                fetch(`${apiBase}/api/categories/${categoryId}/fields`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(fields => {
                        fieldsWrapper.innerHTML = '';
                        if (fields.length === 0) {
                            fieldsContainer.style.display = 'none';
                            return;
                        }

                        // Сортировка полей
                        const desiredOrder = ['brand', 'model'];
                        fields.sort((a, b) => {
                            const indexA = desiredOrder.indexOf(a.key);
                            const indexB = desiredOrder.indexOf(b.key);
                            if (indexA > -1 && indexB > -1) return indexA - indexB;
                            if (indexA > -1) return -1;
                            if (indexB > -1) return 1;
                            return 0;
                        });

                        fields.forEach(field => {
                            const fieldGroup = createFieldElement(field);
                            fieldsWrapper.appendChild(fieldGroup);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading fields:', error);
                        fieldsWrapper.innerHTML = '<p class="text-red-600">Ошибка загрузки характеристик</p>';
                    });
            }

            // --- Логика для связки Марка -> Модель ---
            fieldsWrapper.addEventListener('change', function(event) {
                if (event.target.dataset.fieldKey === 'brand') {
                    const brandId = event.target.value;
                    const modelSelect = fieldsWrapper.querySelector('[data-field-key="model"]');

                    if (!modelSelect) {
                        console.error('Поле для моделей не найдено!');
                        return;
                    }

                    modelSelect.innerHTML = '<option value="">Загрузка...</option>';
                    modelSelect.disabled = true;

                    if (brandId) {
                        fetch(`${apiBase}/api/brands/${brandId}/models`)
                            .then(response => response.json())
                            .then(models => {
                                modelSelect.innerHTML = '<option value="">Выберите модель</option>';

                                // ✅ ИСПРАВЛЕНИЕ ЗДЕСЬ
                                models.forEach(model => {
                                    const option = document.createElement('option');
                                    option.value = model.value; // Было: model.id
                                    option.textContent = model.label; // Было: model.name
                                    modelSelect.appendChild(option);
                                });
                                modelSelect.disabled = false;
                            })
                            .catch(error => {
                                console.error('Ошибка при загрузке моделей:', error);
                                modelSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                                modelSelect.disabled = false;
                            });
                    } else {
                        modelSelect.innerHTML = '<option value="">Сначала выберите марку</option>';
                        modelSelect.disabled = true;
                    }
                }
            });


            // --- Функция создания элементов ---
            function createFieldElement(field) {
                const wrapper = document.createElement('div');
                wrapper.className = 'mb-4';

                const fieldOptions = field.options; // Бэкенд уже отдает массив
                const required = field.is_required ? 'required' : '';
                const requiredLabel = field.is_required ? '<span class="text-red-500">*</span>' : '';
                const dataAttribute = `data-field-key="${field.key}"`;

                let fieldHtml = `<label class="block font-medium text-sm text-gray-700 mb-1">${field.name} ${requiredLabel}</label>`;

                // --- ✅ ГЛАВНОЕ ИСПРАВЛЕНИЕ ЗДЕСЬ ---
                // Мы перехватываем 'brand' и 'model' ДО switch-кейса

                if (field.key === 'brand') {
                    // 1. ЭТО МАРКА (BRAND)
                    // Генерируем <select> используя 'allBrands' из Шага 1

                    const brandOptions = allBrands.map(opt =>
                        `<option value="${opt.value}">${opt.label}</option>`
                    ).join('');

                    fieldHtml += `
                    <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>
                        <option value="">Выберите марку...</option>
                        ${brandOptions}
                    </select>`;

                } else if (field.key === 'model') {
                    // 2. ЭТО МОДЕЛЬ (MODEL)
                    // Генерируем <select>, но пустой и неактивный.
                    // Он заполнится, когда пользователь выберет марку.

                    fieldHtml += `
                    <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute} disabled>
                        <option value="">Сначала выберите марку</option>
                    </select>`;

                } else {
                    // 3. ВСЕ ОСТАЛЬНЫЕ ПОЛЯ (text, number, select)
                    // Используем старую логику

                    switch (field.type) {
                        case 'text':
                            fieldHtml += `<input type="text" name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>`;
                            break;
                        case 'number':
                            fieldHtml += `<input type="number" name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" step="0.01" ${required} ${dataAttribute}>`;
                            break;
                        case 'select':
                            // Это для других <select> (например, "Цвет", "Тип кузова")
                            const options = Array.isArray(fieldOptions)
                                ? fieldOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')
                                : '';

                            fieldHtml += `
                            <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>
                                <option value="">Выберите...</option>
                                ${options}
                            </select>`;
                            break;
                    }
                }

                wrapper.innerHTML = fieldHtml;
                return wrapper;
            }
        });
    </script>
</x-app-layout>

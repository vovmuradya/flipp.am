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

                                    {{-- ✅ ИЗМЕНЕНИЕ: Блок для динамической загрузки категорий --}}
                                    <div id="category-selection-wrapper" class="space-y-4">
                                        {{-- Сюда JavaScript будет добавлять выпадающие списки категорий --}}
                                    </div>
                                    {{-- Скрытое поле, куда записывается ID финальной выбранной категории --}}
                                    <input type="hidden" name="category_id" id="final_category_id">
                                    @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    {{-- Конец блока категорий --}}

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
                                                    {{ $region->current_name ?? $region->name }} {{-- Используем current_name для регионов, если он есть --}}
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
                                    @error('images.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    @error('images')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
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

    {{-- ✅ ИЗМЕНЕНИЕ: Полностью новый JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelectionWrapper = document.getElementById('category-selection-wrapper');
            const finalCategoryIdInput = document.getElementById('final_category_id');
            const fieldsContainer = document.getElementById('custom-fields-container');
            const fieldsWrapper = document.getElementById('fields-wrapper');
            const apiBase = 'http://localhost'; // Убедитесь, что URL правильный

            // Категории верхнего уровня (Транспорт, Недвижимость...)
            // Мы используем $category->current_name, который вы добавили в Модель Category
            const initialCategories = @json($categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->current_name]));

            // Все марки (Бренды)
            const allBrands = @json($brands->map(fn($brand) => ['value' => $brand->id, 'label' => $brand->name_en]));

            // --- 1. ЛОГИКА ДЛЯ КАТЕГОРИЙ ---

            function createCategorySelect(categories, level) {
                const selectId = `category_level_${level}`;

                // Удаляем все select-ы следующих уровней
                let nextLevel = level + 1;
                let nextSelect;
                while (nextSelect = document.getElementById(`category_level_${nextLevel}`)) {
                    nextSelect.parentElement.remove(); // Удаляем div-обертку
                    nextLevel++;
                }

                // Сбрасываем ID и скрываем поля
                finalCategoryIdInput.value = '';
                fieldsContainer.style.display = 'none';
                fieldsWrapper.innerHTML = '';

                // Если детей нет (вернулся пустой массив), значит предыдущий выбор был финальным
                if (categories.length === 0 && level > 0) {
                    const previousSelect = document.getElementById(`category_level_${level - 1}`);
                    if (previousSelect && previousSelect.value) {
                        const finalId = previousSelect.value;
                        finalCategoryIdInput.value = finalId;
                        console.log('Final category selected:', finalId);
                        loadCustomFields(finalId); // Загружаем поля!
                    }
                    return; // Не создаем новый select
                }

                if (categories.length === 0 && level === 0) {
                    categorySelectionWrapper.innerHTML = '<p class="text-red-500">Нет доступных категорий.</p>';
                    return;
                }

                const wrapper = document.createElement('div');
                const labelText = level === 0 ? 'Категория' : 'Подкатегория';
                const requiredStar = level === 0 ? ' <span class="text-red-500">*</span>' : '';

                wrapper.innerHTML = `
                    <label for="${selectId}" class="block font-medium text-sm text-gray-700">${labelText}${requiredStar}</label>
                    <select id="${selectId}" data-level="${level}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" ${level === 0 ? 'required' : ''}>
                        <option value="">-- Выберите --</option>
                        ${categories.map(category => `<option value="${category.id}">${category.name}</option>`).join('')}
                    </select>
                `;

                categorySelectionWrapper.appendChild(wrapper);
                // Навешиваем событие на только что созданный select
                document.getElementById(selectId).addEventListener('change', handleCategoryChange);
            }

            function handleCategoryChange(event) {
                const selectedId = event.target.value;
                const level = parseInt(event.target.dataset.level);

                if (!selectedId) {
                    // Если выбрали "-- Выберите --", удаляем следующие уровни и сбрасываем поля
                    let nextLevel = level + 1;
                    let nextSelect;
                    while (nextSelect = document.getElementById(`category_level_${nextLevel}`)) {
                        nextSelect.parentElement.remove();
                        nextLevel++;
                    }
                    finalCategoryIdInput.value = '';
                    fieldsContainer.style.display = 'none';
                    fieldsWrapper.innerHTML = '';
                    return;
                }

                // Запрашиваем дочерние категории
                fetch(`${apiBase}/api/categories/${selectedId}/children`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(children => {
                        // API должен вернуть 'name' на текущем языке (благодаря current_name в Модели)
                        createCategorySelect(children, level + 1);
                    })
                    .catch(error => console.error('Error loading subcategories:', error));
            }

            // --- 2. ЛОГИКА ДЛЯ ПОЛЕЙ (МАРКА, МОДЕЛЬ, ПОКОЛЕНИЕ) ---

            function loadCustomFields(categoryId) {
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
                        if (!Array.isArray(fields) || fields.length === 0) {
                            fieldsContainer.style.display = 'none';
                            return;
                        }

                        // Сортировка полей
                        const desiredOrder = ['brand', 'model', 'generation'];
                        fields.sort((a, b) => {
                            const indexA = desiredOrder.indexOf(a.key);
                            const indexB = desiredOrder.indexOf(b.key);
                            if (indexA > -1 && indexB > -1) return indexA - indexB;
                            if (indexA > -1) return -1;
                            if (indexB > -1) return 1;
                            return a.name.localeCompare(b.name);
                        });

                        fields.forEach(field => {
                            // API должен вернуть 'name' на текущем языке (благодаря current_name в Модели)
                            const fieldGroup = createFieldElement(field);
                            fieldsWrapper.appendChild(fieldGroup);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading fields:', error);
                        fieldsWrapper.innerHTML = '<p class="text-red-600">Ошибка загрузки характеристик</p>';
                    });
            }

            // --- Логика для связки Марка -> Модель -> Поколение ---
            fieldsWrapper.addEventListener('change', function(event) {
                const fieldKey = event.target.dataset.fieldKey;

                if (fieldKey === 'brand') {
                    const brandId = event.target.value;
                    const modelSelect = fieldsWrapper.querySelector('[data-field-key="model"]');
                    const generationSelect = fieldsWrapper.querySelector('[data-field-key="generation"]');

                    if (modelSelect) {
                        modelSelect.innerHTML = '<option value="">Загрузка...</option>';
                        modelSelect.disabled = true;
                    }
                    if (generationSelect) {
                        generationSelect.innerHTML = '<option value="">Сначала выберите модель</option>';
                        generationSelect.disabled = true;
                    }

                    if (brandId && modelSelect) {
                        fetch(`${apiBase}/api/brands/${brandId}/models`)
                            .then(response => response.json())
                            .then(models => {
                                modelSelect.innerHTML = '<option value="">Выберите модель</option>';
                                models.forEach(model => {
                                    modelSelect.add(new Option(model.label, model.value));
                                });
                                modelSelect.disabled = false;
                            })
                            .catch(error => {
                                console.error('Ошибка при загрузке моделей:', error);
                                modelSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                                modelSelect.disabled = false;
                            });
                    } else if (modelSelect) {
                        modelSelect.innerHTML = '<option value="">Сначала выберите марку</option>';
                        modelSelect.disabled = true;
                    }
                }
                else if (fieldKey === 'model') {
                    const modelId = event.target.value;
                    const generationSelect = fieldsWrapper.querySelector('[data-field-key="generation"]');

                    if (!generationSelect) return;

                    generationSelect.innerHTML = '<option value="">Загрузка...</option>';
                    generationSelect.disabled = true;

                    if (modelId) {
                        fetch(`${apiBase}/api/models/${modelId}/generations`)
                            .then(response => response.json())
                            .then(generations => {
                                generationSelect.innerHTML = '<option value="">Выберите поколение (необязательно)</option>';
                                generations.forEach(gen => {
                                    generationSelect.add(new Option(gen.label, gen.value));
                                });
                                generationSelect.disabled = false;
                            })
                            .catch(error => {
                                console.error('Ошибка при загрузке поколений:', error);
                                generationSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                                generationSelect.disabled = false;
                            });
                    } else {
                        generationSelect.innerHTML = '<option value="">Сначала выберите модель</option>';
                        generationSelect.disabled = true;
                    }
                }
            });

            // --- Функция создания элементов полей ---
            function createFieldElement(field) {
                const wrapper = document.createElement('div');
                wrapper.className = 'mb-4';

                const fieldOptions = field.options;
                const required = field.is_required ? 'required' : '';
                const requiredLabel = field.is_required ? '<span class="text-red-500">*</span>' : '';
                const dataAttribute = `data-field-key="${field.key}"`;

                let fieldHtml = `<label class="block font-medium text-sm text-gray-700 mb-1">${field.name} ${requiredLabel}</label>`;

                if (field.key === 'brand') {
                    const brandOptions = allBrands.map(opt =>
                        `<option value="${opt.value}">${opt.label}</option>`
                    ).join('');
                    fieldHtml += `
                    <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>
                        <option value="">Выберите марку...</option>
                        ${brandOptions}
                    </select>`;
                } else if (field.key === 'model') {
                    fieldHtml += `
                    <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute} disabled>
                        <option value="">Сначала выберите марку</option>
                    </select>`;
                } else if (field.key === 'generation') {
                    fieldHtml += `
                    <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute} disabled>
                        <option value="">Сначала выберите модель</option>
                    </select>`;
                } else {
                    // --- ВСЕ ОСТАЛЬНЫЕ ПОЛЯ ---
                    switch (field.type) {
                        case 'text':
                            fieldHtml += `<input type="text" name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>`;
                            break;
                        case 'number':
                            fieldHtml += `<input type="number" name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" step="any" ${required} ${dataAttribute}>`;
                            break;
                        case 'select':
                            const options = Array.isArray(fieldOptions)
                                ? fieldOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')
                                : '';
                            fieldHtml += `
                            <select name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>
                                <option value="">Выберите...</option>
                                ${options}
                            </select>`;
                            break;
                        // ... (добавьте 'textarea', 'checkbox' по необходимости)
                        default:
                            fieldHtml += `<input type="text" name="custom_fields[${field.id}]" class="block w-full border-gray-300 rounded-md shadow-sm p-2" ${required} ${dataAttribute}>`;
                    }
                }
                wrapper.innerHTML = fieldHtml;
                return wrapper;
            }

            // --- Инициализация: создаем первый Select категорий ---
            createCategorySelect(initialCategories, 0);

        });
    </script>
</x-app-layout>

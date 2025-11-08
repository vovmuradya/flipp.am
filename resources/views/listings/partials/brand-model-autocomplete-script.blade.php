@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const brandInput = document.querySelector('[data-filter="brand"]');
                const modelInput = document.querySelector('[data-filter="model"]');
                const brandSuggestions = document.querySelector('[data-suggestions="brand"]');
                const modelSuggestions = document.querySelector('[data-suggestions="model"]');

                let brandData = [];
                let modelData = [];
                let selectedBrandId = null;

                const modalDelay = 150;

                function closeSuggestions(container) {
                    if (!container) return;
                    container.style.display = 'none';
                    container.innerHTML = '';
                }

                function renderSuggestions(container, items, onSelect) {
                    if (!container) return;
                    container.innerHTML = '';

                    if (!items.length) {
                        closeSuggestions(container);
                        return;
                    }

                    items.forEach(item => {
                        const action = document.createElement('button');
                        action.type = 'button';
                        action.className = 'list-group-item list-group-item-action';
                        action.textContent = item.label;
                        action.dataset.value = item.value;
                        action.addEventListener('click', onSelect);
                        container.appendChild(action);
                    });

                    container.style.display = 'block';
                }

                function normalize(value) {
                    return value.trim().toLowerCase();
                }

                async function fetchBrands() {
                    if (brandData.length) {
                        return brandData;
                    }

                    try {
                        const response = await fetch('{{ url('/api/brands') }}');
                        if (!response.ok) {
                            return [];
                        }

                        brandData = (await response.json())
                            .map(brand => ({
                                value: brand.id,
                                label: brand.name_ru || brand.name_en || brand.name || '',
                            }))
                            .filter(item => item.label !== '');

                        return brandData;
                    } catch (error) {
                        console.warn('Не удалось загрузить марки', error);
                        return [];
                    }
                }

                async function fetchModels(brandId) {
                    modelData = [];

                    if (!brandId) {
                        return modelData;
                    }

                    try {
                        const response = await fetch(`{{ url('/api/brands') }}/${brandId}/models`);
                        if (!response.ok) {
                            return [];
                        }

                        modelData = (await response.json())
                            .map(model => ({
                                value: model.id,
                                label: model.name_ru || model.name_en || model.name || '',
                            }))
                            .filter(item => item.label !== '');

                        return modelData;
                    } catch (error) {
                        console.warn('Не удалось загрузить модели', error);
                        return [];
                    }
                }

                function filterItems(items, term) {
                    const query = normalize(term);
                    if (!query) {
                        return items.slice(0, 10);
                    }

                    return items
                        .filter(item => normalize(item.label).includes(query))
                        .slice(0, 10);
                }

                brandInput?.addEventListener('input', async () => {
                    selectedBrandId = null;
                    modelData = [];
                    if (modelInput) {
                        modelInput.value = '';
                    }
                    closeSuggestions(modelSuggestions);

                    const brands = await fetchBrands();
                    const filtered = filterItems(brands, brandInput.value);

                    renderSuggestions(brandSuggestions, filtered, async (event) => {
                        event.preventDefault();
                        selectedBrandId = event.currentTarget.dataset.value;
                        brandInput.value = event.currentTarget.textContent || '';
                        closeSuggestions(brandSuggestions);
                        modelInput.value = '';
                        await fetchModels(selectedBrandId);
                    });

                    if (!brandInput.value) {
                        closeSuggestions(brandSuggestions);
                    }
                });

                brandInput?.addEventListener('focus', async () => {
                    const brands = await fetchBrands();
                    const filtered = filterItems(brands, brandInput.value);
                    renderSuggestions(brandSuggestions, filtered, async (event) => {
                        event.preventDefault();
                        selectedBrandId = event.currentTarget.dataset.value;
                        brandInput.value = event.currentTarget.textContent || '';
                        closeSuggestions(brandSuggestions);
                        modelInput.value = '';
                        await fetchModels(selectedBrandId);
                    });
                });

                brandInput?.addEventListener('blur', () => {
                    setTimeout(() => closeSuggestions(brandSuggestions), modalDelay);

                    if (!brandInput.value) {
                        selectedBrandId = null;
                        modelData = [];
                        if (modelInput) {
                            modelInput.value = '';
                        }
                        closeSuggestions(modelSuggestions);
                        return;
                    }

                    fetchBrands().then(brands => {
                        const match = brands.find(item => normalize(item.label) === normalize(brandInput.value));
                        if (!match) {
                            brandInput.value = '';
                            selectedBrandId = null;
                            modelData = [];
                            if (modelInput) {
                                modelInput.value = '';
                            }
                            closeSuggestions(modelSuggestions);
                        } else {
                            selectedBrandId = match.value;
                            fetchModels(selectedBrandId);
                        }
                    });
                });

                modelInput?.addEventListener('input', () => {
                    if (!selectedBrandId) {
                        modelInput.value = '';
                        closeSuggestions(modelSuggestions);
                        return;
                    }

                    const filtered = filterItems(modelData, modelInput.value);
                    renderSuggestions(modelSuggestions, filtered, (event) => {
                        event.preventDefault();
                        modelInput.value = event.currentTarget.textContent || '';
                        closeSuggestions(modelSuggestions);
                    });
                });

                modelInput?.addEventListener('focus', () => {
                    const filtered = filterItems(modelData, modelInput.value);
                    renderSuggestions(modelSuggestions, filtered, (event) => {
                        event.preventDefault();
                        modelInput.value = event.currentTarget.textContent || '';
                        closeSuggestions(modelSuggestions);
                    });
                });

                modelInput?.addEventListener('blur', () => {
                    setTimeout(() => closeSuggestions(modelSuggestions), modalDelay);

                    if (!modelInput.value) {
                        return;
                    }

                    const match = modelData.find(item => normalize(item.label) === normalize(modelInput.value));
                    if (!match) {
                        modelInput.value = '';
                    }
                });

                if (brandInput?.value) {
                    fetchBrands().then(async brands => {
                        const match = brands.find(item => normalize(item.label) === normalize(brandInput.value));
                        if (match) {
                            selectedBrandId = match.value;
                            await fetchModels(selectedBrandId);
                        } else {
                            brandInput.value = '';
                        }
                    });
                }
            });
        </script>
    @endpush
@endonce

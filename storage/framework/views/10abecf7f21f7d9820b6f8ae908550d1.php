<?php if (! $__env->hasRenderedOnce('91f79848-f2fb-4313-ae27-88e726d0a289')): $__env->markAsRenderedOnce('91f79848-f2fb-4313-ae27-88e726d0a289'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('listingCreateForm', (config) => ({
                    selectedType: config.initialType ?? null,
                    categoryId: config.initialCategory || '',
                    initialCategory: config.initialCategory || '',
                    categoryMap: config.categoryMap || {},
                    isAuction: Boolean(config.isAuction),
                    locale: config.locale || 'ru',
                    apiEndpoints: config.api || {},
                    colorOptions: config.colors || {},
                    yearOptions: Array.isArray(config.years) ? config.years.map(year => String(year)) : [],
                    titleValue: config.initialTitle || '',
                    engineOptions: config.engineOptions || [],
                    formErrors: {
                        brand: '',
                        model: '',
                        color: '',
                        year: '',
                    },
                    vehicle: {
                        make: config.vehicle?.make || '',
                        model: config.vehicle?.model || '',
                        year: config.vehicle?.year ? String(config.vehicle.year) : '',
                        mileage: config.vehicle?.mileage || '',
                        brandId: config.vehicle?.brand_id ? String(config.vehicle.brand_id) : '',
                        modelId: config.vehicle?.model_id ? String(config.vehicle.model_id) : '',
                        generationId: config.vehicle?.generation_id ? String(config.vehicle.generation_id) : '',
                        engine_displacement_cc: config.vehicle?.engine_displacement_cc ? String(config.vehicle.engine_displacement_cc) : '',
                        exteriorColor: config.vehicle?.exterior_color ? String(config.vehicle.exterior_color) : '',
                        brands: [],
                        models: [],
                        generations: [],
                        loadingBrands: false,
                        loadingModels: false,
                        loadingGenerations: false,
                    },
                    get listingType() {
                        return this.selectedType === 'vehicle' ? 'vehicle' : '';
                    },
                    get formVisible() {
                        return this.isAuction || Boolean(this.selectedType);
                    },
                    async init() {
                        if (this.isAuction && !this.selectedType) {
                            this.selectedType = 'vehicle';
                        }
                        if (this.formVisible) {
                            this.ensureCategoryInScope();
                            this.syncCategoryOptions();
                        }
                        await this.initializeVehicleFormIfNeeded();
                        this.updateAutoTitle(true);

                        this.$watch('selectedType', async (value) => {
                            if (!value) {
                                this.categoryId = '';
                                this.syncCategoryOptions();
                                return;
                            }
                            this.ensureCategoryInScope();
                            this.syncCategoryOptions();
                            if (value === 'vehicle') {
                                await this.initializeVehicleFormIfNeeded(true);
                                this.updateAutoTitle(true);
                            }
                        });

                        this.$watch('categoryId', () => this.syncCategoryOptions());
                        this.$watch('vehicle.brandId', () => this.updateAutoTitle());
                        this.$watch('vehicle.modelId', () => this.updateAutoTitle());
                        this.$watch('vehicle.make', () => this.updateAutoTitle());
                        this.$watch('vehicle.model', () => this.updateAutoTitle());
                    },
                    setType(type) {
                        if (this.selectedType === type) {
                            this.ensureCategoryInScope();
                            this.syncCategoryOptions();
                            return;
                        }
                        this.selectedType = type;
                    },
                    handleSubmit(event) {
                        this.clearClientErrors();

                        if (this.listingType === 'vehicle' && !this.isAuction) {
                            this.syncBrandFromName();
                            this.syncModelFromName();
                            this.updateAutoTitle(true);

                            if (!this.vehicle.year) {
                                this.formErrors.year = 'Выберите год выпуска.';
                            } else if (this.yearOptions.length && !this.yearOptions.includes(String(this.vehicle.year))) {
                                this.formErrors.year = 'Выберите год из списка.';
                            }
                        }

                        if (Object.values(this.formErrors).some(Boolean)) {
                            return;
                        }

                        const form = event.target;
                        window.requestAnimationFrame(() => form.submit());
                    },
                    clearClientErrors() {
                        this.formErrors.brand = '';
                        this.formErrors.model = '';
                        this.formErrors.color = '';
                        this.formErrors.year = '';
                    },
                    ensureCategoryInScope() {
                        if (!this.formVisible) return;
                        const allowed = this.categoryMap[this.selectedType] || [];
                        if (!Array.isArray(allowed) || allowed.length === 0) {
                            if (!this.categoryId) {
                                if (this.initialCategory) {
                                    this.categoryId = String(this.initialCategory);
                                } else {
                                    const first = this.findFirstOptionValue();
                                    if (first) this.categoryId = first;
                                }
                            }
                            return;
                        }
                        const numericCurrent = Number(this.categoryId);
                        if (!allowed.includes(numericCurrent)) {
                            this.categoryId = String(allowed[0]);
                        }
                    },
                    syncCategoryOptions() {
                        if (this.isAuction) return;
                        const select = document.getElementById('category_id');
                        if (!select) return;
                        const allowed = this.categoryMap[this.selectedType] || [];
                        const allowedSet = new Set(allowed);
                        const limitByType = Boolean(this.selectedType) && Array.isArray(allowed) && allowed.length > 0;
                        Array.from(select.options).forEach((option) => {
                            if (!option.value) { option.hidden = false; return; }
                            const sections = option.dataset.sections ? option.dataset.sections.split(',') : [];
                            if (sections.includes('all')) { option.hidden = false; return; }
                            if (!limitByType) { option.hidden = false; return; }
                            const optionId = Number(option.value);
                            const shouldShow = allowedSet.has(optionId);
                            option.hidden = !shouldShow;
                            if (!shouldShow && option.selected) option.selected = false;
                        });
                    },
                    findFirstOptionValue() {
                        const select = document.getElementById('category_id');
                        if (!select) return null;
                        const option = Array.from(select.options).find(opt => opt.value);
                        return option ? String(option.value) : null;
                    },
                    async initializeVehicleFormIfNeeded(force = false) {
                        if (this.isAuction) {
                            return;
                        }
                        if (!force && this.selectedType !== 'vehicle') {
                            return;
                        }
                        await this.ensureBrandsLoaded();
                        if (this.vehicle.brands.length === 0) {
                            return;
                        }
                        if (!this.vehicle.brandId) {
                            this.syncBrandFromName();
                        }
                        if (this.vehicle.brandId) {
                            await this.fetchModels(this.vehicle.brandId, { preserveSelection: true });
                        }
                        if (this.vehicle.modelId) {
                            await this.fetchGenerations(this.vehicle.modelId, { preserveSelection: true });
                        }
                    },
                    async ensureBrandsLoaded() {
                                        if (this.vehicle.loadingBrands || this.vehicle.brands.length > 0) {
                            return;
                        }
                        if (!this.apiEndpoints.brands) {
                            return;
                        }
                        this.vehicle.loadingBrands = true;
                        try {
                            const response = await fetch(this.apiEndpoints.brands);
                            if (!response.ok) {
                                throw new Error('Failed to load brands');
                            }
                            this.vehicle.brands = await response.json();
                            this.refreshBrandDatalist();
                        } catch (error) {
                            console.error('Error loading brands:', error);
                            this.vehicle.brands = [];
                        } finally {
                            this.vehicle.loadingBrands = false;
                        }
                    },
                    async fetchModels(brandId, { preserveSelection = false } = {}) {
                        const modelListEl = document.getElementById('model-options');
                        if (modelListEl) { modelListEl.innerHTML = ''; }
                        if (!brandId || !this.apiEndpoints.models) {
                            this.vehicle.models = [];
                            this.vehicle.modelId = '';
                            return;
                        }
                        this.vehicle.loadingModels = true;
                        try {
                            const url = this.apiUrl('models', { brand: brandId });
                            const response = await fetch(url);
                            if (!response.ok) {
                                throw new Error('Failed to load models');
                            }
                            this.vehicle.models = await response.json();
                            this.refreshModelDatalist();
                            if (preserveSelection) {
                                const exists = this.vehicle.models.some(model => String(model.id) === String(this.vehicle.modelId));
                                if (!exists) {
                                    this.vehicle.modelId = '';
                                }
                            } else {
                                this.vehicle.modelId = '';
                            }
                            if (this.vehicle.modelId) {
                                const model = this.vehicle.models.find(m => String(m.id) === String(this.vehicle.modelId));
                                if (model) {
                                    this.vehicle.model = this.modelLabel(model);
                                }
                            } else {
                                this.syncModelFromName();
                            }
                        } catch (error) {
                            console.error('Error loading models:', error);
                            this.vehicle.models = [];
                            this.vehicle.modelId = '';
                        } finally {
                            this.vehicle.loadingModels = false;
                        }
                    },
                    async fetchGenerations(modelId, { preserveSelection = false } = {}) {
                        if (!modelId || !this.apiEndpoints.generations) {
                            this.vehicle.generations = [];
                            this.vehicle.generationId = '';
                            return;
                        }
                        this.vehicle.loadingGenerations = true;
                        try {
                            const url = this.apiUrl('generations', { model: modelId });
                            const response = await fetch(url);
                            if (!response.ok) {
                                throw new Error('Failed to load generations');
                            }
                            this.vehicle.generations = await response.json();
                            if (preserveSelection) {
                                const exists = this.vehicle.generations.some(gen => String(gen.id) === String(this.vehicle.generationId));
                                if (!exists) {
                                    this.vehicle.generationId = '';
                                }
                            } else {
                                this.vehicle.generationId = '';
                            }
                        } catch (error) {
                            console.error('Error loading generations:', error);
                            this.vehicle.generations = [];
                            this.vehicle.generationId = '';
                        } finally {
                            this.vehicle.loadingGenerations = false;
                        }
                    },
                    apiUrl(key, replacements = {}) {
                        let template = this.apiEndpoints?.[key] || '';
                        Object.entries(replacements).forEach(([token, value]) => {
                            template = template.replace(`{${token}}`, encodeURIComponent(String(value)));
                        });
                        return template;
                    },
                    brandLabel(brand) {
                        if (!brand) return '';
                        return brand.name_ru || brand.name || brand.name_en || '';
                    },
                    brandLabelById(id) {
                        const brand = this.vehicle.brands.find(item => String(item.id) === String(id));
                        return brand ? this.brandLabel(brand) : '';
                    },
                    modelLabel(model) {
                        if (!model) return '';
                        return model.name_ru || model.name || model.name_en || '';
                    },
                    modelLabelById(id) {
                        const model = this.vehicle.models.find(item => String(item.id) === String(id));
                        return model ? this.modelLabel(model) : '';
                    },
                    generationLabel(generation) {
                        if (!generation) return '';
                        const start = generation.year_start || generation.year_begin || '';
                        const end = generation.year_end || generation.year_finish || '';
                        const years = start && end ? `${start}–${end}` : (start || end || '');
                        const title = generation.name || 'Поколение';
                        return years ? `${title} (${years})` : title;
                    },
                    normalizeValue(value) {
                        return (value || '').toString().trim().toLowerCase();
                    },
                    syncBrandFromName() {
                        if (!this.vehicle.make) {
                            this.vehicle.brandId = '';
                            return;
                        }
                        const target = this.normalizeValue(this.vehicle.make);
                        const match = this.vehicle.brands.find(brand => this.normalizeValue(this.brandLabel(brand)) === target);
                        if (match) {
                            const newBrandId = String(match.id);
                            if (this.vehicle.brandId !== newBrandId) {
                                this.vehicle.brandId = newBrandId;
                            }
                            this.vehicle.make = this.brandLabel(match);
                        } else {
                            this.vehicle.brandId = '';
                        }
                    },
                    syncModelFromName() {
                        if (!this.vehicle.model) {
                            this.vehicle.modelId = '';
                            return;
                        }
                        if (this.vehicle.models.length === 0) {
                            this.vehicle.modelId = '';
                            return;
                        }
                        const target = this.normalizeValue(this.vehicle.model);
                        const match = this.vehicle.models.find(model => this.normalizeValue(this.modelLabel(model)) === target);
                        if (match) {
                            const newModelId = String(match.id);
                            if (this.vehicle.modelId !== newModelId) {
                                this.vehicle.modelId = newModelId;
                            }
                            this.vehicle.model = this.modelLabel(match);
                        } else {
                            this.vehicle.modelId = '';
                        }
                    },
                    onBrandInput(event) {
                        const value = event?.target?.value ?? this.vehicle.make ?? '';
                        this.vehicle.make = value;
                        if (!value) {
                            this.vehicle.brandId = '';
                            this.resetModelState();
                            return;
                        }
                        if (this.vehicle.brandId) {
                            const current = this.normalizeValue(this.brandLabelById(this.vehicle.brandId));
                            if (this.normalizeValue(value) !== current) {
                                this.vehicle.brandId = '';
                                this.resetModelState();
                            }
                        }
                    },
                    async onBrandSelected() {
                        const previousBrandId = this.vehicle.brandId;
                        this.syncBrandFromName();
                        if (this.vehicle.brandId) {
                            if (previousBrandId !== this.vehicle.brandId) {
                                this.resetModelState();
                            }
                            await this.fetchModels(this.vehicle.brandId);
                        } else {
                            this.resetModelState();
                        }
                    },
                    onModelInput(event) {
                        const value = event?.target?.value ?? this.vehicle.model ?? '';
                        this.vehicle.model = value;
                        if (!value) {
                            this.vehicle.modelId = '';
                            this.vehicle.generationId = '';
                            this.vehicle.generations = [];
                            return;
                        }
                        if (this.vehicle.modelId) {
                            const current = this.normalizeValue(this.modelLabelById(this.vehicle.modelId));
                            if (this.normalizeValue(value) !== current) {
                                this.vehicle.modelId = '';
                                this.vehicle.generationId = '';
                                this.vehicle.generations = [];
                            }
                        }
                    },
                    async onModelSelected() {
                        const previousModelId = this.vehicle.modelId;
                        this.syncModelFromName();
                        if (this.vehicle.modelId) {
                            if (previousModelId !== this.vehicle.modelId) {
                                this.vehicle.generationId = '';
                                this.vehicle.generations = [];
                            }
                            await this.fetchGenerations(this.vehicle.modelId);
                        } else {
                            this.vehicle.generationId = '';
                            this.vehicle.generations = [];
                        }
                    },
                    resetModelState() {
                        this.vehicle.modelId = '';
                        this.vehicle.model = '';
                        this.vehicle.generations = [];
                        this.vehicle.generationId = '';
                    },
                    handleGenerationChange() {
                        const generation = this.vehicle.generations.find(item => String(item.id) === String(this.vehicle.generationId));
                        if (generation) {
                            const start = generation.year_start || generation.year_begin;
                            if (!this.vehicle.year && start) {
                                this.vehicle.year = String(start);
                            }
                        }
                    },
                    refreshBrandDatalist() {
                        const list = document.getElementById('brand-options');
                        if (!list) return;
                        list.innerHTML = '';
                        this.vehicle.brands.forEach(brand => {
                            const option = document.createElement('option');
                            option.value = this.brandLabel(brand);
                            list.appendChild(option);
                        });
                    },
                    refreshModelDatalist() {
                        const list = document.getElementById('model-options');
                        if (!list) return;
                        list.innerHTML = '';
                        this.vehicle.models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = this.modelLabel(model);
                            list.appendChild(option);
                        });
                    },
                    updateAutoTitle(force = false) {
                        if (this.listingType !== 'vehicle') {
                            if (force && !this.titleValue) {
                                this.titleValue = config.initialTitle || '';
                            }
                            return;
                        }
                        const make = this.vehicle.brandId ? this.brandLabelById(this.vehicle.brandId) : (this.vehicle.make || '');
                        const model = this.vehicle.modelId ? this.modelLabelById(this.vehicle.modelId) : (this.vehicle.model || '');
                        const parts = [make, model].map(part => (part || '').trim()).filter(Boolean);
                        if (parts.length || force) {
                            this.titleValue = parts.join(' ');
                        }
                    },
                    isColorValid(value) {
                        return Object.prototype.hasOwnProperty.call(this.colorOptions, value);
                    },
                }));
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/admin/web/idrom.am/public_html/resources/views/listings/partials/vehicle-form-script.blade.php ENDPATH**/ ?>
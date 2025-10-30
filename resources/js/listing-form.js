document.addEventListener('alpine:init', () => {
    Alpine.data('listingForm', () => ({
        selectedCategory: '',
        selectedBrand: '',
        selectedModel: '',
        carBrands: [],
        carModels: [],
        generations: [],
        isTransportCategory: false,
        locale: document.documentElement.lang || 'ru',

        init() {
            this.selectedCategory = document.getElementById('category_id').value;
            this.checkTransportCategory();

            if (this.isTransportCategory) {
                this.fetchBrands();
            }
        },

        checkTransportCategory() {
            this.isTransportCategory = this.selectedCategory === '11';  // ID категории "Транспорт"
            console.log('isTransportCategory:', this.isTransportCategory);
        },

        async onCategoryChange() {
            this.checkTransportCategory();

            if (this.isTransportCategory && this.carBrands.length === 0) {
                await this.fetchBrands();
            }
        },

        async fetchBrands() {
            try {
                const response = await fetch('/api/brands');
                if (!response.ok) throw new Error('Network response was not ok');
                this.carBrands = await response.json();
                console.log('Loaded brands:', this.carBrands);
            } catch (error) {
                console.error('Error loading brands:', error);
            }
        },

        async onBrandChange() {
            this.selectedModel = '';
            this.carModels = [];
            this.generations = [];

            if (this.selectedBrand) {
                await this.fetchModels();
            }
        },

        async fetchModels() {
            try {
                const response = await fetch(`/api/brands/${this.selectedBrand}/models`);
                if (!response.ok) throw new Error('Network response was not ok');
                this.carModels = await response.json();
                console.log('Loaded models:', this.carModels);
            } catch (error) {
                console.error('Error loading models:', error);
            }
        },

        async onModelChange() {
            this.generations = [];

            if (this.selectedModel) {
                await this.fetchGenerations();
            }
        },

        async fetchGenerations() {
            try {
                const response = await fetch(`/api/models/${this.selectedModel}/generations`);
                if (!response.ok) throw new Error('Network response was not ok');
                this.generations = await response.json();
                console.log('Loaded generations:', this.generations);
            } catch (error) {
                console.error('Error loading generations:', error);
            }
        }
    }));
});

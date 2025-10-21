<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"
             x-data="formEditor({
                initialCategoryId: {{ old('category_id', $listing->category_id) }},
                savedFields: {{ json_encode($savedCustomFields) }},
                existingImages: {{ json_encode($listing->getMedia('images')->map(fn($media) => ['id' => $media->id, 'url' => $media->getUrl('thumb')])) }}
             })"
             x-init="fetchFields()">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Редактирование объявления</h2>

                    <form method="POST" action="{{ route('listings.update', $listing) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-6">

                            {{-- ОСНОВНАЯ ИНФОРМАЦИЯ --}}
                            <div class="p-6 border rounded-lg bg-gray-50 space-y-4">
                                <div>
                                    <label for="title" class="block font-medium text-sm text-gray-700">Заголовок</label>
                                    <input type="text" name="title" id="title" required value="{{ old('title', $listing->title) }}"
                                        @class(['block mt-1 w-full border-gray-300 rounded-md', 'border-red-500' => $errors->has('title')])>
                                    @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="category_id" class="block font-medium text-sm text-gray-700">Категория</label>
                                    <select name="category_id" id="category_id" required x-model="selectedCategory" @change="fetchFields"
                                        @class(['block mt-1 w-full border-gray-300 rounded-md', 'border-red-500' => $errors->has('category_id')])>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="description" class="block font-medium text-sm text-gray-700">Описание</label>
                                    <textarea name="description" id="description" rows="5" required
                                              @class(['block mt-1 w-full border-gray-300 rounded-md', 'border-red-500' => $errors->has('description')])>{{ old('description', $listing->description) }}</textarea>
                                    @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- ХАРАКТЕРИСТИКИ --}}
                            <div class="p-6 border rounded-lg bg-gray-50 space-y-4">
                                <h3 class="text-lg font-semibold mb-4">Характеристики</h3>
                                <template x-if="fieldsLoading"><p class="text-gray-500">Загрузка полей...</p></template>
                                <template x-for="field in customFields" :key="field.id">
                                    <div class="mt-4">
                                        <label :for="'field_' + field.id" class="block font-medium text-sm text-gray-700" x-text="field.name"></label>
                                        <template x-if="field.type === 'number'"><input type="number" :name="'custom_fields[' + field.id + ']'" :value="savedFields[field.id] || ''" class="block mt-1 w-full border-gray-300 rounded-md"></template>
                                        <template x-if="field.type === 'text'"><input type="text" :name="'custom_fields[' + field.id + ']'" :value="savedFields[field.id] || ''" class="block mt-1 w-full border-gray-300 rounded-md"></template>
                                        <template x-if="field.type === 'select'">
                                            <select :name="'custom_fields[' + field.id + ']'" class="block mt-1 w-full border-gray-300 rounded-md">
                                                <template x-for="option in field.options">
                                                    <option :value="option" :selected="option == savedFields[field.id]" x-text="option"></option>
                                                </template>
                                            </select>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- ЦЕНА И МЕСТОПОЛОЖЕНИЕ --}}
                            <div class="p-6 border rounded-lg bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="price" class="block font-medium text-sm text-gray-700">Цена (USD)</label>
                                    <input type="number" name="price" id="price" required value="{{ old('price', $listing->price) }}"
                                        @class(['block mt-1 w-full border-gray-300 rounded-md', 'border-red-500' => $errors->has('price')])>
                                    @error('price')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="region_id" class="block font-medium text-sm text-gray-700">Регион</label>
                                    <select name="region_id" id="region_id" required
                                        @class(['block mt-1 w-full border-gray-300 rounded-md', 'border-red-500' => $errors->has('region_id')])>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" @selected(old('region_id', $listing->region_id) == $region->id)>{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- ЗАГРУЗЧИК ИЗОБРАЖЕНИЙ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Фотографии</h3>
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 mb-4">
                                    <template x-for="image in existingImages" :key="image.id">
                                        <div class="relative">
                                            <img :src="image.url" class="w-full h-24 object-cover rounded-md">
                                            <button @click.prevent="deleteImage(image.id)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">&times;</button>
                                        </div>
                                    </template>
                                </div>
                                <template x-for="id in deletedImageIds" :key="id">
                                    <input type="hidden" name="delete_images[]" :value="id">
                                </template>
                                <h4 class="text-md font-semibold mb-2">Добавить новые фото</h4>
                                <input type="file" name="images[]" id="images" multiple @change="handleFileSelect" accept="image/*" class="hidden">
                                <label for="images" class="cursor-pointer bg-white border border-dashed border-gray-400 rounded-md p-4 w-full flex flex-col items-center justify-center text-gray-500 hover:bg-gray-50"><span>Нажмите для выбора...</span></label>
                                <div x-show="newPreviews.length > 0" class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                    <template x-for="(preview, index) in newPreviews" :key="index">
                                        <div class="relative"><img :src="preview" class="w-full h-24 object-cover rounded-md"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-8">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border-transparent rounded-md font-semibold text-xs text-white">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formEditor(config) {
            return {
                selectedCategory: config.initialCategoryId,
                customFields: [],
                savedFields: config.savedFields,
                fieldsLoading: false,
                existingImages: config.existingImages,
                deletedImageIds: [],
                newPreviews: [],
                fetchFields() {
                    this.fieldsLoading = true;
                    this.customFields = [];
                    if (!this.selectedCategory) {
                        this.fieldsLoading = false;
                        return;
                    }
                    fetch(`/api/categories/${this.selectedCategory}/fields`)
                        .then(response => response.json())
                        .then(data => {
                            this.customFields = data;
                            this.fieldsLoading = false;
                        });
                },
                deleteImage(id) {
                    this.deletedImageIds.push(id);
                    this.existingImages = this.existingImages.filter(img => img.id !== id);
                },
                handleFileSelect(event) {
                    this.newPreviews = [];
                    const files = Array.from(event.target.files);
                    files.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => this.newPreviews.push(e.target.result);
                        reader.readAsDataURL(file);
                    });
                }
            }
        }
    </script>
</x-app-layout>

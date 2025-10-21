<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Новое объявление</h2>

                    <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">

                            {{-- ОСНОВНАЯ ИНФОРМАЦИЯ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Основная информация</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="title" class="block font-medium text-sm text-gray-700">Заголовок</label>
                                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                            @class(['block mt-1 w-full border-gray-300 rounded-md shadow-sm', 'border-red-500' => $errors->has('title')])>
                                        @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="category_id" class="block font-medium text-sm text-gray-700">Категория</label>
                                        <select name="category_id" id="category_id" required
                                            @class(['block mt-1 w-full border-gray-300 rounded-md shadow-sm', 'border-red-500' => $errors->has('category_id')])>
                                            <option value="">Выберите категорию</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="description" class="block font-medium text-sm text-gray-700">Описание</label>
                                        <textarea name="description" id="description" rows="5" required
                                                  @class(['block mt-1 w-full border-gray-300 rounded-md shadow-sm', 'border-red-500' => $errors->has('description')])>{{ old('description') }}</textarea>
                                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ХАРАКТЕРИСТИКИ (ДИНАМИЧЕСКИЙ БЛОК) --}}
                            <div id="custom-fields-container" class="p-6 border rounded-lg bg-gray-50 space-y-4">
                                <h3 class="text-lg font-semibold mb-4">Характеристики</h3>
                                {{-- Сюда будут загружаться поля с помощью JS --}}
                            </div>

                            {{-- ЦЕНА И МЕСТОПОЛОЖЕНИЕ --}}
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <h3 class="text-lg font-semibold mb-4">Цена и местоположение</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="block font-medium text-sm text-gray-700">Цена (USD)</label>
                                        <input type="number" name="price" id="price" value="{{ old('price') }}" required
                                            @class(['block mt-1 w-full border-gray-300 rounded-md shadow-sm', 'border-red-500' => $errors->has('price')])>
                                        @error('price')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="region_id" class="block font-medium text-sm text-gray-700">Регион</label>
                                        <select name="region_id" id="region_id" required
                                            @class(['block mt-1 w-full border-gray-300 rounded-md shadow-sm', 'border-red-500' => $errors->has('region_id')])>
                                            <option value="">Выберите регион</option>
                                            @foreach($regions as $region)
                                                <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>{{ $region->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('region_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ЗАГРУЗЧИК ИЗОБРАЖЕНИЙ --}}
                            <div class="p-6 border rounded-lg bg-gray-50" x-data="imageUploader()">
                                {{-- ... (код загрузчика изображений остаётся без изменений) ... --}}
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Опубликовать
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Этот скрипт остаётся таким же, как я давал ранее
        document.addEventListener('DOMContentLoaded', function () { /* ... */ });

        // Новый JS-компонент для загрузчика изображений
        function imageUploader() {
            return {
                previews: [],
                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    files.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews.push(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    });
                },
                removeImage(index) {
                    // This is a simplified way to handle removal.
                    // A more robust solution would manage the FileList object.
                    this.previews.splice(index, 1);
                    const dt = new DataTransfer();
                    const input = document.getElementById('images');
                    const { files } = input;

                    for (let i = 0; i < files.length; i++) {
                        if (i !== index) {
                            dt.items.add(files[i]);
                        }
                    }
                    input.files = dt.files;
                }
            }
        }
    </script>
</x-app-layout>

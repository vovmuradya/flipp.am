<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Редактирование объявления</h2>

                    <form method="POST" action="{{ route('listings.update', $listing) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mt-4">
                            <x-input-label for="title" value="Заголовок" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $listing->title)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="category_id" value="Категория" />
                            <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $listing->category_id) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="region_id" value="Регион" />
                            <select name="region_id" id="region_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" @selected(old('region_id', $listing->region_id) == $region->id)>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="price" value="Цена (USD)" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $listing->price)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" value="Описание" />
                            <textarea name="description" id="description" rows="6" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $listing->description) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="images" value="Изображения (при загрузке новых, старые будут заменены)" />
                            <input id="images" name="images[]" type="file" multiple class="block w-full text-sm text-slate-500 mt-1 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                Сохранить изменения
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

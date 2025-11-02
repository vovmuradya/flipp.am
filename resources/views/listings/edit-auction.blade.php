<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Редактирование аукционного объявления') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('listings.update', $listing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Основная информация</h3>
                            <p class="text-sm text-gray-600">Вы можете изменить цену и описание. Остальные данные заблокированы, так как они были получены с аукциона.</p>
                        </div>

                        <!-- Title (read-only) -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Заголовок</label>
                            <input type="text" id="title" value="{{ $listing->title }}" readonly
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700">Цена (AMD)</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $listing->price) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $listing->description) }}</textarea>
                             @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('dashboard.my-auctions') }}" class="text-sm text-gray-600 hover:underline mr-4">
                                Отмена
                            </a>

                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


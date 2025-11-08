<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Редактирование аукционного объявления') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">{{ __('Обнаружены ошибки:') }}</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('auction-listings.update', $listing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Основная информация') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('Вы можете изменить только цену и описание для аукционного объявления.') }}</p>
                            </div>

                            <!-- Title (read-only) -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Заголовок') }}</label>
                                <input type="text" id="title" value="{{ $listing->title }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                            </div>

                            <!-- Vehicle Details (read-only) -->
                            @if($listing->vehicleDetail)
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h4 class="font-medium text-gray-800 mb-2">{{ __('Характеристики (нередактируемые)') }}</h4>
                                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                        <dt class="text-gray-500">{{ __('Марка:') }}</dt><dd class="text-gray-900">{{ $listing->vehicleDetail->make }}</dd>
                                        <dt class="text-gray-500">{{ __('Модель:') }}</dt><dd class="text-gray-900">{{ $listing->vehicleDetail->model }}</dd>
                                        <dt class="text-gray-500">{{ __('Год:') }}</dt><dd class="text-gray-900">{{ $listing->vehicleDetail->year }}</dd>
                                        <dt class="text-gray-500">{{ __('Пробег:') }}</dt><dd class="text-gray-900">{{ number_format($listing->vehicleDetail->mileage) }} {{ __('км') }}</dd>
                                    </dl>
                                    <a href="{{ $listing->vehicleDetail->source_auction_url }}" target="_blank" class="text-blue-600 hover:underline text-sm mt-2 inline-block">{{ __('Посмотреть на аукционе') }}</a>
                                </div>
                            @endif

                            <!-- Price (editable) -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">{{ __('Цена (AMD)') }}</label>
                                <input type="number" name="price" id="price" value="{{ old('price', $listing->price) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description (editable) -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Описание') }}</label>
                                <textarea name="description" id="description" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $listing->description) }}</textarea>
                                @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Images (read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Фотографии') }}</label>
                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @forelse($listing->getMedia('images') as $media)
                                        <div class="relative">
                                            <img src="{{ $media->getUrl('thumb') }}" alt="{{ __('Фото') }}" class="w-full h-32 object-cover rounded-lg">
                                        </div>
                                    @empty
                                        <p class="text-gray-500 col-span-full">{{ __('Фотографии отсутствуют.') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('auction-listings.index') }}" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300">
                                {{ __('Отмена') }}
                            </a>
                            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                                {{ __('Сохранить изменения') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

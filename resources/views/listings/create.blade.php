@extends('layouts.app')

@section('content')
    @if($auctionData)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
            <p class="font-bold">Отладка данных аукциона:</p>
            <pre>{{ print_r($auctionData, true) }}</pre>
        </div>
    @endif
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Заголовок (разный для аукциона и обычной формы) -->
                <div class="px-6 py-4 {{ $auctionData ? 'bg-blue-600' : 'bg-gray-800' }} text-white">
                    <h1 class="text-2xl font-bold">
                        {{ $auctionData ? '🚗 Создать объявление с аукциона' : 'Создать объявление' }}
                    </h1>
                    @if($auctionData && isset($auctionData['auction_url']))
                        <p class="text-sm mt-1 opacity-90">
                            Источник: <a href="{{ $auctionData['auction_url'] }}" target="_blank" class="underline hover:text-blue-200">{{ $auctionData['auction_url'] }}</a>
                        </p>
                    @endif
                </div>

                <div class="p-6">
                    <!-- Галерея фото с аукциона -->
                    @if($auctionData && isset($auctionData['photos']) && count($auctionData['photos']) > 0)
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border-2 border-blue-200">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">
                                📸 Фотографии с аукциона ({{ count($auctionData['photos']) }})
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($auctionData['photos'] as $index => $photo)
                                    <div class="relative group">
                                        <img src="{{ $photo }}"
                                             alt="Фото {{ $index + 1 }}"
                                             class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer"
                                             onclick="window.open('{{ $photo }}', '_blank')"
                                             onerror="this.onerror=null; this.src='https://placehold.co/300x200/e5e7eb/6b7280?text=Photo+{{ $index + 1 }}';">
                                        <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded">
                                            #{{ $index + 1 }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-600 mt-3 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Фотографии будут автоматически загружены. Нажмите на фото для просмотра в полном размере.
                            </p>
                        </div>
                    @endif

                    <!-- Ошибки валидации -->
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="font-medium text-red-700">Исправьте следующие ошибки:</p>
                                    <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('listings.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Скрытые поля для аукциона -->
                        @if($auctionData)
                            <input type="hidden" name="from_auction" value="1">
                            <input type="hidden" name="auction_url" value="{{ $auctionData['auction_url'] ?? '' }}">

                            @if(isset($auctionData['photos']))
                                @foreach($auctionData['photos'] as $photo)
                                    <input type="hidden" name="auction_photos[]" value="{{ $photo }}">
                                @endforeach
                            @endif
                        @endif

                        <!-- Заголовок -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                Заголовок <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="title"
                                   id="title"
                                   required
                                   value="{{ old('title', $auctionData['title'] ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Описание <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="6"
                                      required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $auctionData['description'] ?? '') }}</textarea>
                        </div>

                        <!-- Цена -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">
                                Цена (AMD) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="price"
                                   id="price"
                                   required
                                   min="0"
                                   placeholder="Укажите цену"
                                   value="{{ old('price', $auctionData['price'] ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Категория -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">
                                Категория <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id"
                                    id="category_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $auctionData['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->localized_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Поля автомобиля -->
                        <!-- Поля автомобиля -->
                        <div id="vehicle-fields"
                             style="{{ $auctionData || old('category_id') == 1 ? 'display: block;' : 'display: none;' }}"
                             class="space-y-6 p-6 {{ $auctionData ? 'bg-blue-50 border-2 border-blue-300' : 'bg-gray-50' }} rounded-lg">

                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                                Характеристики автомобиля
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Марка -->
                                <div>
                                    <label for="vehicle_make" class="block text-sm font-medium text-gray-700">Марка</label>
                                    <input type="text"
                                           name="vehicle[make]"
                                           id="vehicle_make"
                                           value="{{ old('vehicle.make', $auctionData['vehicle']['make'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Модель -->
                                <div>
                                    <label for="vehicle_model" class="block text-sm font-medium text-gray-700">Модель</label>
                                    <input type="text"
                                           name="vehicle[model]"
                                           id="vehicle_model"
                                           value="{{ old('vehicle.model', $auctionData['vehicle']['model'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Год выпуска -->
                                <div>
                                    <label for="vehicle_year" class="block text-sm font-medium text-gray-700">Год выпуска</label>
                                    <input type="number"
                                           name="vehicle[year]"
                                           id="vehicle_year"
                                           min="1900"
                                           max="{{ date('Y') + 1 }}"
                                           value="{{ old('vehicle.year', $auctionData['vehicle']['year'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Пробег -->
                                <div>
                                    <label for="vehicle_mileage" class="block text-sm font-medium text-gray-700">Пробег (км)</label>
                                    <input type="number"
                                           name="vehicle[mileage]"
                                           id="vehicle_mileage"
                                           min="0"
                                           value="{{ old('vehicle.mileage', $auctionData['vehicle']['mileage'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Тип кузова -->
                                <div>
                                    <label for="vehicle_body_type" class="block text-sm font-medium text-gray-700">Тип кузова</label>
                                    <select name="vehicle[body_type]"
                                            id="vehicle_body_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Выберите тип кузова</option>
                                        @php
                                            $bodyTypes = [
                                                'sedan' => 'Седан',
                                                'hatchback' => 'Хэтчбек',
                                                'SUV' => 'Внедорожник',
                                                'pickup' => 'Пикап',
                                                'coupe' => 'Купе',
                                                'convertible' => 'Кабриолет',
                                                'wagon' => 'Универсал',
                                                'van' => 'Фургон'
                                            ];
                                            $selectedBodyType = old('vehicle.body_type', $auctionData['vehicle']['body_type'] ?? '');
                                        @endphp
                                        @foreach($bodyTypes as $value => $label)
                                            <option value="{{ $value }}" {{ $selectedBodyType == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Коробка передач -->
                                <div>
                                    <label for="vehicle_transmission" class="block text-sm font-medium text-gray-700">Коробка передач</label>
                                    <select name="vehicle[transmission]"
                                            id="vehicle_transmission"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Выберите коробку</option>
                                        @php
                                            $transmissions = [
                                                'automatic' => 'Автоматическая',
                                                'manual' => 'Механическая',
                                                'semi-automatic' => 'Роботизированная',
                                                'cvt' => 'Вариатор'
                                            ];
                                            $selectedTransmission = old('vehicle.transmission', $auctionData['vehicle']['transmission'] ?? '');
                                        @endphp
                                        @foreach($transmissions as $value => $label)
                                            <option value="{{ $value }}" {{ $selectedTransmission == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Тип топлива -->
                                <div>
                                    <label for="vehicle_fuel" class="block text-sm font-medium text-gray-700">Тип топлива</label>
                                    <select name="vehicle[fuel_type]"
                                            id="vehicle_fuel"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Выберите тип топлива</option>
                                        @php
                                            $fuelTypes = [
                                                'gasoline' => 'Бензин',
                                                'diesel' => 'Дизель',
                                                'hybrid' => 'Гибрид',
                                                'electric' => 'Электро',
                                                'lpg' => 'Газ'
                                            ];
                                            $selectedFuelType = old('vehicle.fuel_type', $auctionData['vehicle']['fuel_type'] ?? '');
                                        @endphp
                                        @foreach($fuelTypes as $value => $label)
                                            <option value="{{ $value }}" {{ $selectedFuelType == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Объем двигателя -->
                                <div>
                                    <label for="vehicle_engine" class="block text-sm font-medium text-gray-700">Объем двигателя (куб. см)</label>
                                    <input type="number"
                                           name="vehicle[engine_displacement_cc]"
                                           id="vehicle_engine"
                                           min="0"
                                           value="{{ old('vehicle.engine_displacement_cc', $auctionData['vehicle']['engine_displacement_cc'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Цвет кузова -->
                                <div>
                                    <label for="vehicle_color" class="block text-sm font-medium text-gray-700">Цвет кузова</label>
                                    <input type="text"
                                           name="vehicle[exterior_color]"
                                           id="vehicle_color"
                                           value="{{ old('vehicle.exterior_color', $auctionData['vehicle']['exterior_color'] ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>


                        <!-- Регион -->
                        <div>
                            <label for="region_id" class="block text-sm font-medium text-gray-700">
                                Регион <span class="text-red-500">*</span>
                            </label>
                            <select name="region_id"
                                    id="region_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Выберите регион</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('region_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Загрузка изображений (только если НЕ с аукциона) -->
                        @if(!$auctionData)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Изображения</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                                <span>Загрузить файлы</span>
                                                <input id="images" name="images[]" type="file" multiple accept="image/*" class="sr-only">
                                            </label>
                                            <p class="pl-1">или перетащите их сюда</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, WEBP до 5MB</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Кнопки -->
                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <a href="{{ route('home') }}" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition">
                                Отмена
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition">
                                {{ $auctionData ? '🚀 Создать объявление с аукциона' : 'Создать объявление' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const vehicleFields = document.getElementById('vehicle-fields');

            categorySelect.addEventListener('change', function () {
                vehicleFields.style.display = (this.value == '1') ? 'block' : 'none';
            });
        });
    </script>
@endsection

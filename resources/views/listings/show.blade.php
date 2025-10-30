<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- ЗАГОЛОВОК --}}
                    <h1 class="text-3xl font-bold">{{ $listing->title }}</h1>

                    {{-- ЕДИНЫЙ БЛОК КНОПОК УПРАВЛЕНИЯ --}}
                    <div class="mt-4 flex items-center space-x-2">
                        @auth
                            <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-full border hover:bg-gray-100">
                                    @if(auth()->user()->favorites->contains($listing))
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                                    @endif
                                </button>
                            </form>
                        @endauth
                        @can('update', $listing)
                            <a href="{{ route('listings.edit', $listing) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">Редактировать</a>
                        @endcan
                        @can('delete', $listing)
                            <form action="{{ route('listings.destroy', $listing) }}" method="POST" onsubmit="return confirm('Вы уверены?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Удалить</button>
                            </form>
                        @endcan
                    </div>

                    {{-- ГАЛЕРЕЯ, ИНФОРМАЦИЯ, ЦЕНА, ОПИСАНИЕ --}}
                    <div class="mt-6" x-data="{ mainImage: '{{ $listing->getFirstMediaUrl('images', 'medium') ?: '' }}' }">
                        @if($listing->hasMedia('images'))
                            {{-- Главное изображение --}}
                            <div class="mb-4">
                                <img :src="mainImage" alt="{{ $listing->title }}" class="rounded-lg shadow-lg w-full object-cover">
                            </div>
                            {{-- Миниатюры --}}
                            <div class="grid grid-cols-5 gap-2">
                                @foreach($listing->getMedia('images') as $media)
                                    <div @click="mainImage = '{{ $media->getUrl('medium') }}'" class="cursor-pointer border-2 rounded-lg" :class="{ 'border-blue-500': mainImage === '{{ $media->getUrl('medium') }}' }">
                                        <img src="{{ $media->getUrl('thumb') }}" alt="thumbnail" class="w-full h-24 object-cover rounded-md">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Заглушка --}}
                        @endif
                    </div>
                    <div class="mt-4 text-gray-600">
                        <span>Опубликовано: {{ $listing->created_at->format('d.m.Y') }}</span> |
                        <span>В: {{ $listing->region?->name }}</span> |
                        <span>Продавец: {{ $listing->user?->name }}</span>
                    </div>
                    <div class="my-6 text-4xl font-extrabold text-indigo-600">${{ number_format($listing->price, 0, '.', ' ') }}</div>
                    <div class="mt-8 prose max-w-none">
                        <h2 class="text-xl font-bold">Описание</h2>
                        <p>{{ $listing->description }}</p>
                    </div>

                    {{-- ТЗ v2.1: Характеристики автомобиля --}}
                    @if($listing->listing_type === 'vehicle' && $listing->vehicleDetail)
                        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h2 class="text-2xl font-bold mb-4 text-blue-900">Характеристики автомобиля</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Марка:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->make }}</strong>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Модель:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->model }}</strong>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Год выпуска:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->year }}</strong>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Пробег:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->formatted_mileage }}</strong>
                                </div>

                                @if($listing->vehicleDetail->transmission)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Коробка передач:</span>
                                    <strong class="text-gray-900">
                                        @switch($listing->vehicleDetail->transmission)
                                            @case('automatic') Автоматическая @break
                                            @case('manual') Механическая @break
                                            @case('cvt') CVT @break
                                            @case('semi-automatic') Полуавтоматическая @break
                                            @default {{ $listing->vehicleDetail->transmission }}
                                        @endswitch
                                    </strong>
                                </div>
                                @endif

                                @if($listing->vehicleDetail->fuel_type)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Тип топлива:</span>
                                    <strong class="text-gray-900">
                                        @switch($listing->vehicleDetail->fuel_type)
                                            @case('gasoline') Бензин @break
                                            @case('diesel') Дизель @break
                                            @case('hybrid') Гибрид @break
                                            @case('electric') Электро @break
                                            @case('lpg') ГБО @break
                                            @default {{ $listing->vehicleDetail->fuel_type }}
                                        @endswitch
                                    </strong>
                                </div>
                                @endif

                                @if($listing->vehicleDetail->body_type)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Тип кузова:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->body_type }}</strong>
                                </div>
                                @endif

                                @if($listing->vehicleDetail->engine_displacement_cc)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Объём двигателя:</span>
                                    <strong class="text-gray-900">{{ number_format($listing->vehicleDetail->engine_displacement_cc) }} см³</strong>
                                </div>
                                @endif

                                @if($listing->vehicleDetail->exterior_color)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600 font-medium">Цвет:</span>
                                    <strong class="text-gray-900">{{ $listing->vehicleDetail->exterior_color }}</strong>
                                </div>
                                @endif
                            </div>

                            {{-- ТЗ v2.1: Кнопка "Смотреть на аукционе" --}}
                            @if($listing->vehicleDetail->is_from_auction && $listing->vehicleDetail->source_auction_url)
                                <div class="mt-6 pt-4 border-t border-blue-300">
                                    <a href="{{ $listing->vehicleDetail->source_auction_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Посмотреть оригинальное объявление на аукционе
                                    </a>
                                    <p class="mt-2 text-sm text-blue-700">
                                        <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Это объявление было создано на основе лота с аукциона
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($listing->customFieldValues->isNotEmpty())
                        <div class="mt-6">
                            <h4 class="text-xl font-bold mb-4">Характеристики</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                                @foreach($listing->customFieldValues as $customValue)
                                    <div>
                                        <span class="text-gray-600">{{ $customValue->field->name }}:</span>
                                        <strong class="text-gray-900 ml-2">{{ $customValue->value }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- БЛОК СООБЩЕНИЙ --}}
                    @auth
                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-xl font-bold mb-4">Связаться с продавцом</h3>

                            @if(auth()->id() === $listing->user_id)
                                <p class="text-gray-500">Это ваше объявление.</p>
                            @else
                                @if(session('success_message'))
                                    <div class="mb-4 text-green-600 font-semibold">{{ session('success_message') }}</div>
                                @endif
                                <form action="{{ route('listings.messages.store', $listing) }}" method="POST">
                                    @csrf
                                    <div>
                                        <textarea name="body" rows="4" class="w-full border-gray-300 rounded-md" placeholder="Напишите ваше сообщение..." required minlength="10"></textarea>
                                        @error('body')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="mt-4">
                                        <x-primary-button>Отправить сообщение</x-primary-button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endauth

                    {{-- БЛОК ДЛЯ ОТЗЫВОВ --}}
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-xl font-bold mb-4">Отзывы о продавце</h3>

                        {{-- Список уже оставленных отзывов --}}
                        <div class="space-y-4">
                            @forelse($listing->reviews as $review)
                                <div class="border-b pb-2">
                                    <div class="flex items-center mb-1">
                                        <span class="font-semibold">{{ $review->reviewer->name }}</span>
                                        <div class="ml-2 flex text-yellow-400">
                                            @for ($i = 0; $i < $review->rating; $i++) ★ @endfor
                                        </div>
                                    </div>
                                    <p class="text-gray-700">{{ $review->comment }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $review->created_at->format('d.m.Y') }}</p>
                                </div>
                            @empty
                                <p class="text-gray-500">Отзывов пока нет.</p>
                            @endforelse
                        </div>

                        {{-- Форма для добавления нового отзыва --}}
                        @auth
                            @if(auth()->id() !== $listing->user_id && !$listing->reviews->contains('reviewer_id', auth()->id()))
                                <div class="mt-6">
                                    <h4 class="text-lg font-semibold mb-2">Оставить отзыв</h4>
                                    @if(session('success'))<div class="mb-4 text-green-600 font-semibold">{{ session('success') }}</div>@endif
                                    @if(session('error'))<div class="mb-4 text-red-600 font-semibold">{{ session('error') }}</div>@endif
                                    <form action="{{ route('listings.reviews.store', $listing) }}" method="POST">
                                        @csrf
                                        <div class="flex items-center space-x-1 text-gray-400 flex-row-reverse justify-end">
                                            <input type="radio" name="rating" value="5" class="hidden peer" id="rate-5" required><label for="rate-5" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                            <input type="radio" name="rating" value="4" class="hidden peer" id="rate-4"><label for="rate-4" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                            <input type="radio" name="rating" value="3" class="hidden peer" id="rate-3"><label for="rate-3" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                            <input type="radio" name="rating" value="2" class="hidden peer" id="rate-2"><label for="rate-2" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                            <input type="radio" name="rating" value="1" class="hidden peer" id="rate-1"><label for="rate-1" class="text-2xl cursor-pointer peer-hover:text-yellow-400 peer-checked:text-yellow-400">★</label>
                                            <label class="mr-2">Оценка:</label>
                                        </div>
                                        @error('rating')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                        <div class="mt-4">
                                            <textarea name="comment" rows="4" class="w-full border-gray-300 rounded-md" placeholder="Напишите ваш отзыв..." required minlength="10"></textarea>
                                            @error('comment')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="mt-4"><x-primary-button>Отправить отзыв</x-primary-button></div>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Избранное') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse ($listings as $listing)
                        <div class="flex justify-between items-center border-b py-4">
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('listings.show', $listing) }}" class="flex-shrink-0">
                                    @php
                                        $thumb = $listing->getFirstMediaUrl('images', 'thumb');
                                    @endphp

                                    @if ($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $listing->title }}" class="w-24 h-24 object-cover rounded-md shadow-sm">
                                    @else
                                        {{-- Заглушка, если нет фото --}}
                                        <div class="w-24 h-24 bg-gray-200 flex items-center justify-center rounded-md shadow-sm text-gray-400 text-sm">
                                            Нет фото
                                        </div>
                                    @endif
                                </a>
                                <div>
                                    <a href="{{ route('listings.show', $listing) }}" class="text-lg font-semibold text-indigo-600 hover:text-indigo-800">
                                        {{ $listing->title }}
                                    </a>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span>Категория: {{ $listing->category->name }}</span>
                                    </div>
                                    <div class="text-lg font-bold text-gray-800 mt-2">
                                        ${{ number_format($listing->price, 0) }}
                                    </div>
                                </div>
                            </div>
                            {{-- Кнопка для удаления из избранного --}}
                            <form action="{{ route('listings.favorite.toggle', $listing) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-red-500 hover:underline">Удалить из избранного</button>
                            </form>
                        </div>
                    @empty
                        <p>Вы еще ничего не добавили в избранное.</p>
                    @endforelse

                    <div class="mt-8">
                        {{ $listings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

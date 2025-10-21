<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мои объявления') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse ($listings as $listing)
                        <div class="flex justify-between items-center border-b py-4">

                            {{-- Блок с картинкой и информацией --}}
                            <div class="flex items-center space-x-4">

                                {{-- Картинка --}}
                                <a href="{{ route('listings.show', $listing) }}" class="flex-shrink-0">
                                    @if($listing->hasMedia('images'))
                                        <img src="{{ $listing->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $listing->title }}" class="w-24 h-24 object-cover rounded-md shadow-sm">
                                    @else
                                        {{-- Заглушка, если нет фото --}}
                                        <div class="w-24 h-24 bg-gray-200 rounded-md flex items-center justify-center text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                {{-- Информация --}}
                                <div>
                                    <a href="{{ route('listings.show', $listing) }}" class="text-lg font-semibold text-indigo-600 hover:text-indigo-800">
                                        {{ $listing->title }}
                                    </a>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span>Категория: {{ $listing->category->name }}</span>
                                        <span class="mx-2">|</span>
                                        <span>Статус: <span class="font-bold">{{ $listing->status }}</span></span>
                                        <span class="mx-2">|</span>
                                        <span>Опубликовано: {{ $listing->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Кнопки управления --}}
                            <div class="flex space-x-2 flex-shrink-0">
                                <a href="{{ route('listings.edit', $listing) }}" class="text-blue-500 hover:underline">Редактировать</a>
                                <form action="{{ route('listings.destroy', $listing) }}" method="POST" onsubmit="return confirm('Вы уверены?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Удалить</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p>У вас пока нет объявлений.</p>
                    @endforelse

                    <div class="mt-8">
                        {{ $listings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

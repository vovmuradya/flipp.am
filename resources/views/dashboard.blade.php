<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Личный кабинет') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-xl font-semibold mb-2">{{ __('Активные объявления') }}</div>
                        <div class="text-3xl font-bold text-indigo-600">
                            {{ auth()->user()->listings()->active()->count() }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-xl font-semibold mb-2">{{ __('На модерации') }}</div>
                        <div class="text-3xl font-bold text-yellow-600">
                            {{ auth()->user()->listings()->where('status', 'pending')->count() }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-xl font-semibold mb-2">{{ __('Просмотры за неделю') }}</div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ auth()->user()->listings()->sum('views_count') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Быстрые действия') }}</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('listings.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ __('Создать объявление') }}
                        </a>

                        <a href="{{ route('dashboard.favorites') }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            {{ __('Избранное') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Последние объявления -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Ваши последние объявления') }}</h3>
                    <div class="space-y-4">
                        @forelse(auth()->user()->listings()->latest()->take(5)->get() as $listing)
                            <div class="flex items-center justify-between border-b pb-4 last:border-b-0">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-16 h-16">
                                        <img src="{{ $listing->getMainImageUrl('thumb') }}"
                                             alt="{{ $listing->title }}"
                                             class="w-full h-full object-cover rounded">
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-medium">{{ $listing->title }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ __('Просмотров:') }} {{ $listing->views_count }} |
                                            {{ __('Статус:') }}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $listing->status === 'active' ? 'bg-green-100 text-green-800' :
                                                   ($listing->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ __($listing->status) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('listings.edit', $listing) }}"
                                       class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('Редактировать') }}
                                    </a>
                                    <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('{{ __('Вы уверены?') }}')">
                                            {{ __('Удалить') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-500">
                                {{ __('У вас пока нет объявлений') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

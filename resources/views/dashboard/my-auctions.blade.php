<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мои аукционные объявления') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Список ваших объявлений с аукционов</h3>
                        <a href="{{ route('listings.create-from-auction') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Добавить новое
                        </a>
                    </div>

                    @if($listings->isEmpty())
                        <p>У вас пока нет объявлений с аукционов.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Фото
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Заголовок
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Статус
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Действия</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($listings as $listing)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img src="{{ $listing->getFirstMediaUrl('images', 'thumb') ?: 'https://placehold.co/100x80/e5e7eb/6b7280?text=No+Image' }}" alt="{{ $listing->title }}" class="h-16 w-20 object-cover rounded">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $listing->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $listing->created_at->format('d.m.Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($listing->price, 0, '.', ' ') }} {{ $listing->currency }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $listing->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ __($listing->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('auction-listings.edit', $listing) }}" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                                            <form action="{{ route('auction-listings.destroy', $listing) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Вы уверены, что хотите удалить это объявление?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $listings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


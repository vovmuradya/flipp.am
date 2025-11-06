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
                                        $fallbackImage = asset('images/no-image.svg');
                                    @endphp
                                    @if($listing->hasMedia('images'))
                                        <img src="{{ $listing->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $listing->title }}" class="w-24 h-24 object-cover rounded-md shadow-sm">
                                    @else
                                        <img src="{{ $fallbackImage }}" alt="Нет изображения" class="w-24 h-24 object-cover rounded-md shadow-sm">
                                    @endif
                                </a>
                                <div>
                                    <a href="{{ route('listings.show', $listing) }}" class="text-lg font-semibold text-indigo-600 hover:text-indigo-800">
                                        {{ $listing->title }}
                                    </a>
                                    <div class="text-sm text-gray-500 mt-1">
                                        @php
                                            $cat = $listing->category ?? null;
                                            if ($cat) {
                                                if (isset($cat->localized_name)) {
                                                    $catName = $cat->localized_name;
                                                } else {
                                                    $raw = $cat->name ?? '';
                                                    if (is_string($raw)) {
                                                        $dec = json_decode($raw, true);
                                                        $catName = is_array($dec) ? ($dec[app()->getLocale()] ?? $dec['ru'] ?? $dec['en'] ?? array_values($dec)[0] ?? '') : $raw;
                                                    } elseif ($raw instanceof \Illuminate\Support\Collection) {
                                                        $arr = $raw->toArray();
                                                        $catName = $arr[app()->getLocale()] ?? $arr['ru'] ?? $arr['en'] ?? (array_values($arr)[0] ?? '');
                                                    } elseif (is_array($raw)) {
                                                        $catName = $raw[app()->getLocale()] ?? $raw['ru'] ?? $raw['en'] ?? (array_values($raw)[0] ?? '');
                                                    } elseif (is_object($raw)) {
                                                        $arr = (array) $raw;
                                                        $catName = $arr[app()->getLocale()] ?? $arr['ru'] ?? $arr['en'] ?? (array_values($arr)[0] ?? '');
                                                    } else {
                                                        $catName = (string)$raw;
                                                    }
                                                }
                                            } else {
                                                $catName = '—';
                                            }
                                        @endphp
                                        <span>Категория: {{ $catName }}</span>
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

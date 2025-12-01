@php
    $titleParts = [];
    if ($filters['brand']) { $titleParts[] = $filters['brand']; }
    if ($filters['model']) { $titleParts[] = $filters['model']; }
    if ($filters['fuel_type']) { $titleParts[] = strtoupper($filters['fuel_type']); }
    if ($filters['body_type']) { $titleParts[] = $filters['body_type']; }
    if ($filters['price_to']) { $titleParts[] = __('до :price', ['price' => number_format($filters['price_to'], 0, '.', ' ')]); }
    if ($filters['price_from']) { $titleParts[] = __('от :price', ['price' => number_format($filters['price_from'], 0, '.', ' ')]); }
    if ($filters['year_from'] && $filters['year_to'] && $filters['year_from'] === $filters['year_to']) {
        $titleParts[] = $filters['year_from'];
    }
    $pageTitle = implode(' ', $titleParts) ?: __('Авто по фильтрам');
@endphp

<x-app-layout>
    @push('head')
        <title>{{ $pageTitle }} | flipp-am</title>
        <meta name="description" content="{{ __('Подборка автомобилей по заданным критериям: :filters', ['filters' => implode(', ', array_filter($titleParts)) ?: __('каталог')]) }}">
        <link rel="canonical" href="{{ url()->current() }}">
    @endpush

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-sm text-slate-500">Programmatic SEO landing</p>
                <h1 class="text-3xl font-bold text-slate-900">{{ $pageTitle }}</h1>
                <p class="text-slate-600 mt-2">
                    {{ __('Подборка автомобилей по заданным критериям.') }}
                </p>
            </div>

            @if($listings->isEmpty())
                <div class="bg-white shadow rounded-lg p-6 text-slate-600">
                    {{ __('Ничего не найдено по этим параметрам. Попробуйте другую комбинацию.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                        <x-listing.card :listing="$listing" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $listings->links() }}
                </div>

                <div class="mt-10 bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">{{ __('FAQ и подбор') }}</h2>
                    <ul class="list-disc list-inside text-slate-700 space-y-2">
                        <li>{{ __('Цены обновляются автоматически; проверяйте бейджи Great/Fair/Overpriced для ориентира.') }}</li>
                        <li>{{ __('Используйте фильтры по топливу, кузову, году и цене прямо в URL (например, diesel/under-15000).') }}</li>
                        <li>{{ __('Нажмите на объявление, чтобы увидеть VIN/характеристики и итоговую стоимость (OTD).') }}</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

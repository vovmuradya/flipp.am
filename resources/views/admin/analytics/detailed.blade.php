@extends('admin.layout')

@section('title', 'Детальная аналитика')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Детальная аналитика</h2>
    </div>

    <!-- Форма фильтрации -->
    <form method="GET" action="{{ route('admin.analytics.detailed') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">Дата начала</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">Дата окончания</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Применить</button>
            <a href="{{ route('admin.analytics.detailed') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Сбросить</a>
        </div>
    </form>

    <!-- Основные метрики -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-number">{{ number_format($basicStats['total_visits'], 0, '', ' ') }}</div>
            <div class="stat-label">Всего посещений</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($basicStats['unique_visits'], 0, '', ' ') }}</div>
            <div class="stat-label">Уникальных посетителей</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($basicStats['new_users'], 0, '', ' ') }}</div>
            <div class="stat-label">Новых пользователей</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($basicStats['new_listings'], 0, '', ' ') }}</div>
            <div class="stat-label">Новых объявлений</div>
        </div>
    </div>

    <!-- Конверсии -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Конверсии</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Регистрации / Посещения:</span>
                    <span class="font-medium">{{ $conversionStats['registration_conversion_rate'] }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>Объявления / Регистрации:</span>
                    <span class="font-medium">{{ $conversionStats['listing_conversion_rate'] }}%</span>
                </div>
                <div class="flex justify-between">
                    <span>Всего посещений:</span>
                    <span class="font-medium">{{ number_format($conversionStats['visits'], 0, '', ' ') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Всего регистраций:</span>
                    <span class="font-medium">{{ number_format($conversionStats['registrations'], 0, '', ' ') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Создано объявлений:</span>
                    <span class="font-medium">{{ number_format($conversionStats['listings_created'], 0, '', ' ') }}</span>
                </div>
            </div>
        </div>

        <!-- Типы устройств -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">По типам устройств</h3>
            <div class="space-y-2">
                @forelse($trafficStats['device_stats'] as $device)
                <div class="flex justify-between">
                    <span>{{ ucfirst($device->device_type) }}:</span>
                    <span class="font-medium">{{ $device->count }}</span>
                </div>
                @empty
                <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>

        <!-- Браузеры -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">По браузерам</h3>
            <div class="space-y-2">
                @forelse($trafficStats['browser_stats'] as $browser)
                <div class="flex justify-between">
                    <span>{{ $browser->browser }}:</span>
                    <span class="font-medium">{{ $browser->count }}</span>
                </div>
                @empty
                <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Источники трафика -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Источники трафика</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($trafficSources['referrers'] as $referrer)
                <div class="flex justify-between">
                    <span class="truncate max-w-xs" title="{{ $referrer->referrer }}">{{ $referrer->referrer }}:</span>
                    <span class="font-medium">{{ $referrer->count }}</span>
                </div>
                @empty
                <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>

        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">UTM источники</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($trafficSources['utm_sources'] as $utm)
                <div class="flex justify-between">
                    <span>{{ $utm->utm_source }}:</span>
                    <span class="font-medium">{{ $utm->count }}</span>
                </div>
                @empty
                <p class="text-gray-500">Нет данных</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Популярные страницы -->
    <div class="admin-card mb-6">
        <h3 class="text-lg font-semibold mb-4">Популярные страницы</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Страница</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Просмотры</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Уникальные</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($popularPages as $page)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $page->page_url }}">
                            {{ $page->page_url }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $page->count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $page->unique_views }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Нет данных
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
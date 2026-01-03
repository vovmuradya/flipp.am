@extends('admin.layout')

@section('title', 'Аналитика')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ number_format($totalUsers, 0, '', ' ') }}</div>
        <div class="stat-label">Всего пользователей</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ number_format($totalListings, 0, '', ' ') }}</div>
        <div class="stat-label">Всего объявлений</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ number_format($pendingListings, 0, '', ' ') }}</div>
        <div class="stat-label">На модерации</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ number_format($activeUsersToday, 0, '', ' ') }}</div>
        <div class="stat-label">Сегодня онлайн</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Недавние пользователи</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата регистрации</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentUsers as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('d.m.Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' :
                                   ($user->role === 'dealer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ $user->role === 'admin' ? 'Админ' :
                                   ($user->role === 'dealer' ? 'Дилер' : 'Частное лицо') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Просмотреть всех</a>
        </div>
    </div>

    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Недавние объявления</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentListings as $listing)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ Str::limit($listing->title, 20) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $listing->status === 'active' ? 'bg-green-100 text-green-800' :
                                   ($listing->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   ($listing->status === 'rejected' ? 'bg-red-100 text-red-800' :
                                   ($listing->status === 'suppressed' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'))) }}">
                                {{ $listing->status === 'active' ? 'Активно' :
                                   ($listing->status === 'pending' ? 'На модерации' :
                                   ($listing->status === 'rejected' ? 'Отклонено' :
                                   ($listing->status === 'suppressed' ? 'Подавлено' : 'Истекло'))) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $listing->created_at->format('d.m.Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($listing->price, 0, '', ' ') }} {{ $listing->currency ?? 'AMD' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right">
            <a href="{{ route('admin.listings.index') }}" class="text-blue-600 hover:underline">Просмотреть все</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Быстрые действия</h3>
        <div class="space-y-2">
            <a href="{{ route('admin.moderation.index') }}" class="block px-4 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                Модерация объявлений ({{ $pendingListings }})
            </a>
            <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                Управление пользователями
            </a>
            <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                Управление категориями
            </a>
        </div>
    </div>

    <div class="admin-card lg:col-span-2">
        <h3 class="text-lg font-semibold mb-4">Статистика по трафику</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $realTimeStats['visits_last_hour'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Посещений за последний час</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $realTimeStats['active_users'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Активных пользователей</div>
            </div>
        </div>

        <div class="mt-4">
            <h4 class="font-medium mb-2">Типы устройств:</h4>
            <div class="space-y-1">
                @forelse($trafficStats['device_stats'] as $device)
                <div class="flex justify-between text-sm">
                    <span>{{ ucfirst($device->device_type) }}:</span>
                    <span>{{ $device->count }}</span>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Нет данных</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="admin-card mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Детальная аналитика</h3>
        <a href="{{ route('admin.analytics.detailed') }}" class="text-blue-600 hover:underline">Просмотреть все</a>
    </div>
    <p class="text-gray-600">Детализированная статистика посещений, источников трафика, конверсий и популярных страниц.</p>
</div>
@endsection
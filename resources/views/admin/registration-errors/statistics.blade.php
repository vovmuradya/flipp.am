@extends('admin.layout')

@section('title', 'Статистика ошибок регистрации')

@section('content')
<div class="stats-grid mb-6">
    <div class="stat-card">
        <div class="stat-number">{{ $statistics['total_errors'] }}</div>
        <div class="stat-label">Всего ошибок</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $statistics['resolved_errors'] }}</div>
        <div class="stat-label">Решено</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $statistics['unresolved_errors'] }}</div>
        <div class="stat-label">Не решено</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $statistics['resolved_errors'] > 0 ? round(($statistics['resolved_errors'] / $statistics['total_errors']) * 100, 2) : 0 }}%</div>
        <div class="stat-label">Процент решенных</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Ошибки по типам</h3>
        <div class="space-y-2">
            @forelse($topIssues as $issue)
            <div class="flex justify-between items-center">
                <span>{{ $issue->error_type }}</span>
                <div class="flex items-center">
                    <span class="mr-2 text-sm">{{ $issue->count }}</span>
                    <div class="w-32 bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $statistics['total_errors'] > 0 ? ($issue->count / $statistics['total_errors']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500">Нет данных</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Рекомендации по улучшению</h3>
        <div class="space-y-3">
            <div class="p-3 bg-blue-50 rounded">
                <h4 class="font-medium text-blue-800">Для ошибок валидации</h4>
                <ul class="mt-1 text-sm text-blue-700 list-disc pl-5">
                    <li>Улучшить клиентскую валидацию</li>
                    <li>Добавить подсказки для полей</li>
                    <li>Реализовать проверку в реальном времени</li>
                </ul>
            </div>
            
            <div class="p-3 bg-yellow-50 rounded">
                <h4 class="font-medium text-yellow-800">Для дублирования данных</h4>
                <ul class="mt-1 text-sm text-yellow-700 list-disc pl-5">
                    <li>Добавить проверку существования до отправки формы</li>
                    <li>Реализовать уникальные ограничения в базе данных</li>
                    <li>Улучшить сообщения об ошибках</li>
                </ul>
            </div>
            
            <div class="p-3 bg-green-50 rounded">
                <h4 class="font-medium text-green-800">Для технических ошибок</h4>
                <ul class="mt-1 text-sm text-green-700 list-disc pl-5">
                    <li>Проверить конфигурацию сервера</li>
                    <li>Обновить зависимости</li>
                    <li>Добавить больше логирования</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <h3 class="text-lg font-semibold mb-4">Активность за последние 7 дней</h3>
    <div class="h-64 flex items-center justify-center bg-gray-100 rounded">
        <p>График ошибок регистрации (реализация в следующей версии)</p>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Недавние ошибки</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentErrors as $error)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $error->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $error->type_badge }}">
                            {{ $error->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $error->email ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $error->occurred_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $error->is_resolved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $error->is_resolved ? 'Решена' : 'Не решена' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Нет недавних ошибок
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
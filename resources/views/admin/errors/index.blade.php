@extends('admin.layout')

@section('title', 'Лог ошибок')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $totalErrors }}</div>
        <div class="stat-label">Всего ошибок</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $registrationErrors }}</div>
        <div class="stat-label">Ошибки регистрации</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $applicationErrors }}</div>
        <div class="stat-label">Ошибки приложения</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">12</div>
        <div class="stat-label">Сегодня</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Ошибки регистрации</h3>
        <p class="mb-4">Проблемы, с которыми сталкиваются пользователи при регистрации на платформе.</p>
        <a href="{{ route('admin.errors.registration') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Просмотреть ошибки регистрации</a>
    </div>

    <div class="admin-card">
        <h3 class="text-lg font-semibold mb-4">Ошибки приложения</h3>
        <p class="mb-4">Технические ошибки, возникающие в работе системы.</p>
        <a href="{{ route('admin.errors.application') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Просмотреть ошибки приложения</a>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Последние ошибки</h3>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сообщение</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Регистрация
                        </span>
                    </td>
                    <td class="px-6 py-4">Неверный формат email</td>
                    <td class="px-6 py-4 whitespace-nowrap">2 часа назад</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.errors.registration') }}" class="text-blue-600 hover:text-blue-900">Просмотреть</a>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Приложение
                        </span>
                    </td>
                    <td class="px-6 py-4">SQLSTATE[23000]: Integrity constraint violation</td>
                    <td class="px-6 py-4 whitespace-nowrap">1 час назад</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.errors.application') }}" class="text-blue-600 hover:text-blue-900">Просмотреть</a>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Регистрация
                        </span>
                    </td>
                    <td class="px-6 py-4">Пароль слишком короткий</td>
                    <td class="px-6 py-4 whitespace-nowrap">5 часов назад</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.errors.registration') }}" class="text-blue-600 hover:text-blue-900">Просмотреть</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Рекомендации по улучшению</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 p-4 rounded">
            <h4 class="font-medium text-blue-800">Для ошибок регистрации</h4>
            <ul class="mt-2 space-y-1 text-sm">
                <li>• Улучшить валидацию на фронтенде</li>
                <li>• Добавить подсказки для паролей</li>
                <li>• Реализовать проверку email в реальном времени</li>
                <li>• Добавить OAuth авторизацию</li>
            </ul>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded">
            <h4 class="font-medium text-yellow-800">Для ошибок приложения</h4>
            <ul class="mt-2 space-y-1 text-sm">
                <li>• Добавить проверки на null значения</li>
                <li>• Улучшить валидацию данных</li>
                <li>• Обновить зависимости</li>
                <li>• Добавить больше логирования</li>
            </ul>
        </div>
    </div>
</div>
@endsection
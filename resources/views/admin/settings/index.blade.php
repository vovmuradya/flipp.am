@extends('admin.layout')

@section('title', 'Настройки')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Настройки приложения</h2>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-700">Название приложения</label>
                <input type="text" name="app_name" id="app_name" value="{{ config('app.name', 'Idrom.am') }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            
            <div>
                <label for="app_locale" class="block text-sm font-medium text-gray-700">Язык по умолчанию</label>
                <select name="app_locale" id="app_locale" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>Русский</option>
                    <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>Английский</option>
                    <option value="hy" {{ app()->getLocale() === 'hy' ? 'selected' : '' }}>Армянский</option>
                </select>
            </div>
            
            <div>
                <label for="maintenance_mode" class="block text-sm font-medium text-gray-700">Режим обслуживания</label>
                <select name="maintenance_mode" id="maintenance_mode" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="0">Выключен</option>
                    <option value="1">Включен</option>
                </select>
            </div>
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Сохранить настройки</button>
            <a href="{{ route('admin.settings.clear-cache') }}" 
               class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700"
               onclick="event.preventDefault(); document.getElementById('clear-cache-form').submit();">
                Очистить кэш
            </a>
        </div>
    </form>
    
    <form id="clear-cache-form" action="{{ route('admin.settings.clear-cache') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Информация о системе</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><strong>Версия Laravel:</strong> {{ app()->version() }}</p>
            <p><strong>Версия PHP:</strong> {{ phpversion() }}</p>
            <p><strong>Сервер:</strong> {{ request()->server('SERVER_SOFTWARE') ?? 'Неизвестно' }}</p>
        </div>
        <div>
            <p><strong>Временная зона:</strong> {{ config('app.timezone') }}</p>
            <p><strong>Режим отладки:</strong> {{ config('app.debug') ? 'Включен' : 'Выключен' }}</p>
            <p><strong>База данных:</strong> {{ config('database.default') }}</p>
        </div>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Дополнительные действия</h3>
    
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('admin.users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Управление пользователями</a>
        <a href="{{ route('admin.listings.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Управление объявлениями</a>
        <a href="{{ route('admin.categories.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Управление категориями</a>
        <a href="{{ route('admin.moderation.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">Модерация</a>
    </div>
</div>
@endsection
@extends('admin.layout')

@section('title', 'Редактирование пользователя')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Редактирование пользователя: {{ $user->name }}</h2>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Основная роль</label>
                <select name="role" id="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="individual" {{ old('role', $user->role) === 'individual' ? 'selected' : '' }}>Частное лицо</option>
                    <option value="dealer" {{ old('role', $user->role) === 'dealer' ? 'selected' : '' }}>Дилер</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_dealer" id="is_dealer" value="1"
                       {{ old('is_dealer', $user->is_dealer) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="is_dealer" class="ml-2 block text-sm text-gray-700">Является дилером</label>
            </div>
        </div>

        <!-- Управление ролями пользователя -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Роли пользователя</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($roles as $role)
                <div class="flex items-center">
                    <input type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}"
                           {{ $user->hasRole($role->name) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-700">
                        {{ $role->display_name ?? $role->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Сохранить</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Информация о пользователе</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Дата регистрации:</strong> {{ $user->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Последний вход:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Нет данных' }}</p>
            <p><strong>Статус верификации:</strong>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $user->email_verified_at ? 'Подтвержден' : 'Не подтвержден' }}
                </span>
            </p>
        </div>
        <div>
            <p><strong>Количество объявлений:</strong> {{ $user->listings->count() }}</p>
            <p><strong>Телефон:</strong> {{ $user->phone ?? 'Не указан' }}</p>
            <p><strong>Часовой пояс:</strong> {{ $user->timezone ?? 'По умолчанию' }}</p>
            <p><strong>Язык:</strong> {{ $user->language ?? 'По умолчанию' }}</p>
        </div>
    </div>
</div>
@endsection
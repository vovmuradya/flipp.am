@extends('admin.layout')

@section('title', 'Управление пользователями')

@section('content')
<div class="admin-card mb-6">
    <h2 class="text-xl font-semibold mb-4">Фильтр пользователей</h2>

    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
            <input type="text" name="name" id="name" value="{{ request('name') }}"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="text" name="email" id="email" value="{{ request('email') }}"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Роль</label>
            <select name="role" id="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все роли</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Администратор</option>
                <option value="dealer" {{ request('role') === 'dealer' ? 'selected' : '' }}>Дилер</option>
                <option value="individual" {{ request('role') === 'individual' ? 'selected' : '' }}>Частное лицо</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Фильтровать</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Сбросить</a>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Пользователи</h2>
        <p class="text-gray-600">Всего: {{ $users->total() }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роли</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата регистрации</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' :
                               ($user->role === 'dealer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                            {{ $user->role === 'admin' ? 'Админ' :
                               ($user->role === 'dealer' ? 'Дилер' : 'Частное лицо') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @foreach($user->roles as $role)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 mr-1 mb-1">
                                {{ $role->display_name ?? $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Редактировать</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
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

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
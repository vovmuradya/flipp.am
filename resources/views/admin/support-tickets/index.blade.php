@extends('admin.layout')

@section('title', 'Тикеты поддержки')

@section('content')
<div class="admin-card mb-6">
    <h2 class="text-xl font-semibold mb-4">Фильтр тикетов</h2>
    
    <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Статус</label>
            <select name="status" id="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все статусы</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Открыт</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Решен</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Закрыт</option>
                <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Ожидает</option>
            </select>
        </div>
        
        <div>
            <label for="priority" class="block text-sm font-medium text-gray-700">Приоритет</label>
            <select name="priority" id="priority" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все приоритеты</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Низкий</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Средний</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Высокий</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Критический</option>
            </select>
        </div>
        
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Категория</label>
            <select name="category" id="category" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все категории</option>
                <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Технический</option>
                <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Платежи</option>
                <option value="account" {{ request('category') === 'account' ? 'selected' : '' }}>Аккаунт</option>
                <option value="listing" {{ request('category') === 'listing' ? 'selected' : '' }}>Объявления</option>
                <option value="verification" {{ request('category') === 'verification' ? 'selected' : '' }}>Верификация</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Другое</option>
            </select>
        </div>
        
        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700">Пользователь</label>
            <select name="user_id" id="user_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все пользователи</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
                @endforeach
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Фильтровать</button>
            <a href="{{ route('admin.support-tickets.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Сбросить</a>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Тикеты поддержки</h2>
        <a href="{{ route('admin.support-tickets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Создать тикет</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тема</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Приоритет</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Назначен</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $ticket->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->user->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="{{ $ticket->subject }}">
                        {{ $ticket->subject }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->priority_badge }}">
                            {{ $ticket->priority_text }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->status_badge }}">
                            {{ $ticket->status_text }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->category_text }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->assignedUser->name ?? 'Не назначен' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Просмотр</a>
                        <a href="{{ route('admin.support-tickets.edit', $ticket->id) }}" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Нет тикетов поддержки
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
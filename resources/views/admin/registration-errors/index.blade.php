@extends('admin.layout')

@section('title', 'Ошибки регистрации')

@section('content')
<div class="admin-card mb-6">
    <h2 class="text-xl font-semibold mb-4">Фильтр ошибок</h2>
    
    <form method="GET" action="{{ route('admin.registration-errors.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="error_type" class="block text-sm font-medium text-gray-700">Тип ошибки</label>
            <select name="error_type" id="error_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все типы</option>
                <option value="validation" {{ request('error_type') === 'validation' ? 'selected' : '' }}>Ошибка валидации</option>
                <option value="duplicate" {{ request('error_type') === 'duplicate' ? 'selected' : '' }}>Дублирование данных</option>
                <option value="technical" {{ request('error_type') === 'technical' ? 'selected' : '' }}>Техническая ошибка</option>
                <option value="email_verification" {{ request('error_type') === 'email_verification' ? 'selected' : '' }}>Ошибка верификации email</option>
                <option value="other" {{ request('error_type') === 'other' ? 'selected' : '' }}>Другое</option>
            </select>
        </div>
        
        <div>
            <label for="resolved" class="block text-sm font-medium text-gray-700">Статус решения</label>
            <select name="resolved" id="resolved" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Все</option>
                <option value="true" {{ request('resolved') === 'true' ? 'selected' : '' }}>Решенные</option>
                <option value="false" {{ request('resolved') === 'false' ? 'selected' : '' }}>Нерешенные</option>
            </select>
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ request('email') }}"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div>
            <label for="ip_address" class="block text-sm font-medium text-gray-700">IP-адрес</label>
            <input type="text" name="ip_address" id="ip_address" value="{{ request('ip_address') }}"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 mr-2">Фильтровать</button>
            <a href="{{ route('admin.registration-errors.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Сбросить</a>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Ошибки регистрации</h2>
        <div class="text-gray-600">
            Всего: {{ $errors->total() }} | 
            Решенные: {{ $statistics['resolved_errors'] ?? 0 }} | 
            Нерешенные: {{ $statistics['unresolved_errors'] ?? 0 }}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сообщение</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP-адрес</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($errors as $error)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $error->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $error->email ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $error->type_badge }}">
                            {{ $error->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $error->error_message }}">
                        {{ $error->error_message }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $error->ip_address }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $error->is_resolved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $error->is_resolved ? 'Решена' : 'Не решена' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $error->occurred_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.registration-errors.show', $error->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Просмотр</a>
                        @if(!$error->is_resolved)
                        <form method="POST" action="{{ route('admin.registration-errors.resolve', $error->id) }}" class="inline"
                              onsubmit="return confirm('Вы уверены, что хотите отметить эту ошибку как решенную?')">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-900">Отметить как решенную</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.registration-errors.unresolve', $error->id) }}" class="inline"
                              onsubmit="return confirm('Вы уверены, что хотите отметить эту ошибку как нерешенную?')">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-900">Отметить как нерешенную</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Нет ошибок регистрации
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $errors->links() }}
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Очистка старых ошибок</h3>
    <form method="POST" action="{{ route('admin.registration-errors.cleanup') }}" 
          onsubmit="return confirm('Вы уверены, что хотите удалить старые ошибки регистрации?')">
        @csrf
        <div class="flex items-center">
            <label for="days" class="mr-2">Удалить ошибки старше</label>
            <input type="number" name="days" id="days" value="30" min="1" class="border border-gray-300 rounded p-1 mr-2 w-20">
            <span class="mr-2">дней</span>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Очистить</button>
        </div>
    </form>
</div>
@endsection
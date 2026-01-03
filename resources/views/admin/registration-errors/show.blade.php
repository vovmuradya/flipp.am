@extends('admin.layout')

@section('title', 'Ошибка регистрации #' . $error->id)

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Ошибка регистрации #{{ $error->id }}</h2>
        <a href="{{ route('admin.registration-errors.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Назад к списку</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Информация об ошибке</h3>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">Email:</span>
                    <span>{{ $error->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Имя пользователя:</span>
                    <span>{{ $error->username ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Тип ошибки:</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $error->type_badge }}">
                        {{ $error->type_label }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Код ошибки:</span>
                    <span>{{ $error->error_code ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">IP-адрес:</span>
                    <span>{{ $error->ip_address }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">User Agent:</span>
                    <span class="truncate max-w-xs" title="{{ $error->user_agent }}">{{ $error->user_agent ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Статус и решение</h3>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">Статус:</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $error->is_resolved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $error->is_resolved ? 'Решена' : 'Не решена' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Дата ошибки:</span>
                    <span>{{ $error->occurred_at->format('d.m.Y H:i:s') }}</span>
                </div>
                @if($error->is_resolved)
                <div class="flex justify-between">
                    <span class="font-medium">Дата решения:</span>
                    <span>{{ $error->resolved_at->format('d.m.Y H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Решена:</span>
                    <span>{{ $error->resolver->name ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Сообщение об ошибке</h3>
        <div class="bg-red-50 p-4 rounded-md">
            <p class="text-gray-700">{{ $error->error_message }}</p>
        </div>
    </div>

    @if($error->validation_errors)
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ошибки валидации</h3>
        <div class="bg-yellow-50 p-4 rounded-md">
            <pre class="text-gray-700 text-sm">{{ json_encode($error->validation_errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    @if($error->request_data)
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Данные запроса</h3>
        <div class="bg-gray-50 p-4 rounded-md">
            <pre class="text-gray-700 text-sm">{{ json_encode($error->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    @if($error->resolution_notes)
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Заметки о решении</h3>
        <div class="bg-green-50 p-4 rounded-md">
            <p class="text-gray-700">{{ $error->resolution_notes }}</p>
        </div>
    </div>
    @endif

    <div class="flex space-x-4">
        @if($error->is_resolved)
        <form method="POST" action="{{ route('admin.registration-errors.unresolve', $error->id) }}" class="inline"
              onsubmit="return confirm('Вы уверены, что хотите отметить эту ошибку как нерешенную?')">
            @csrf
            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">Отметить как нерешенную</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.registration-errors.resolve', $error->id) }}" class="inline"
              onsubmit="return confirm('Вы уверены, что хотите отметить эту ошибку как решенную?')">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Отметить как решенную</button>
        </form>
        @endif
        
        <form method="POST" action="{{ route('admin.registration-errors.resolve', $error->id) }}" class="inline">
            @csrf
            <input type="hidden" name="notes" value="Проверено, проблема устранена">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Быстро решить</button>
        </form>
    </div>
</div>
@endsection
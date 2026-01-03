@extends('admin.layout')

@section('title', 'Лог ошибок регистрации')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Ошибки регистрации пользователей</h2>
        <a href="{{ route('admin.errors.clear-logs') }}" 
           class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
           onclick="return confirm('Вы уверены, что хотите очистить логи ошибок?')">
            Очистить логи
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сообщение об ошибке</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP-адрес</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($errors as $error)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['id'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['user_email'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['error_message'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['timestamp']->format('d.m.Y H:i:s') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['ip_address'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Анализ ошибок регистрации</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 p-4 rounded">
            <h4 class="font-medium text-blue-800">Самые частые ошибки</h4>
            <ul class="mt-2 space-y-1">
                <li class="text-sm">• Неверный формат email - 42%</li>
                <li class="text-sm">• Пароль слишком короткий - 28%</li>
                <li class="text-sm">• Пользователь уже существует - 20%</li>
                <li class="text-sm">• Другие ошибки - 10%</li>
            </ul>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded">
            <h4 class="font-medium text-yellow-800">Рекомендации</h4>
            <ul class="mt-2 space-y-1">
                <li class="text-sm">• Улучшить валидацию на фронтенде</li>
                <li class="text-sm">• Добавить подсказки для паролей</li>
                <li class="text-sm">• Реализовать проверку email в реальном времени</li>
            </ul>
        </div>
        
        <div class="bg-green-50 p-4 rounded">
            <h4 class="font-medium text-green-800">Улучшения</h4>
            <ul class="mt-2 space-y-1">
                <li class="text-sm">• Добавить OAuth авторизацию</li>
                <li class="text-sm">• Реализовать подтверждение email</li>
                <li class="text-sm">• Добавить защиту от ботов</li>
            </ul>
        </div>
    </div>
</div>
@endsection
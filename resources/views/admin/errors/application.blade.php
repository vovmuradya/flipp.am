@extends('admin.layout')

@section('title', 'Лог ошибок приложения')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Ошибки приложения</h2>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Уровень</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сообщение об ошибке</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Файл</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($errors as $error)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['id'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $error['level'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $error['level'] === 'error' ? 'Ошибка' : 'Предупреждение' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['error_message'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['timestamp']->format('d.m.Y H:i:s') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $error['file'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Статистика ошибок</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-red-50 p-4 rounded text-center">
            <div class="text-2xl font-bold text-red-800">24</div>
            <div class="text-sm text-red-600">Ошибки</div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded text-center">
            <div class="text-2xl font-bold text-yellow-800">36</div>
            <div class="text-sm text-yellow-600">Предупреждения</div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded text-center">
            <div class="text-2xl font-bold text-blue-800">12</div>
            <div class="text-sm text-blue-600">Сегодня</div>
        </div>
        
        <div class="bg-green-50 p-4 rounded text-center">
            <div class="text-2xl font-bold text-green-800">156</div>
            <div class="text-sm text-green-600">Всего</div>
        </div>
    </div>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Анализ ошибок</h3>
    
    <div class="space-y-4">
        <div>
            <h4 class="font-medium">Частые ошибки</h4>
            <ul class="mt-2 space-y-1 text-sm">
                <li>• SQLSTATE[23000]: Integrity constraint violation - 15%</li>
                <li>• Trying to access array offset on value of type null - 12%</li>
                <li>• Call to undefined method - 8%</li>
                <li>• Undefined index - 7%</li>
            </ul>
        </div>
        
        <div>
            <h4 class="font-medium">Рекомендации по улучшению</h4>
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
@extends('admin.layout')

@section('title', 'Интеграция Idram и imID')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Интеграция Idram и imID</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Статистика транзакций</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Всего транзакций:</span>
                    <span class="font-medium">{{ $transactions->total() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Успешных:</span>
                    <span class="font-medium text-green-600">
                        {{ $transactions->getCollection()->where('status', 'completed')->count() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>В ожидании:</span>
                    <span class="font-medium text-yellow-600">
                        {{ $transactions->getCollection()->where('status', 'pending')->count() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Неудачных:</span>
                    <span class="font-medium text-red-600">
                        {{ $transactions->getCollection()->where('status', 'failed')->count() }}
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.idram-imid.transactions') }}" class="text-blue-600 hover:underline">Просмотреть все транзакции</a>
            </div>
        </div>

        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Статистика верификации</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Всего запросов:</span>
                    <span class="font-medium">{{ $verificationRequests->total() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Верифицированных:</span>
                    <span class="font-medium text-green-600">
                        {{ $verificationRequests->getCollection()->where('status', 'verified')->count() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>В ожидании:</span>
                    <span class="font-medium text-yellow-600">
                        {{ $verificationRequests->getCollection()->where('status', 'pending')->count() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Отклоненных:</span>
                    <span class="font-medium text-red-600">
                        {{ $verificationRequests->getCollection()->where('status', 'rejected')->count() }}
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.idram-imid.verification-requests') }}" class="text-blue-600 hover:underline">Просмотреть все запросы</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Последние транзакции</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $transaction->transaction_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->user->name ?? 'Гость' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : 
                                       'bg-gray-100 text-gray-800')) }}">
                                    {{ $transaction->status === 'completed' ? 'Завершена' : 
                                       ($transaction->status === 'pending' ? 'В ожидании' : 
                                       ($transaction->status === 'failed' ? 'Ошибка' : 'Отменена')) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Нет транзакций
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <h3 class="text-lg font-semibold mb-4">Последние запросы верификации</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($verificationRequests as $imIdUser)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $imIdUser->imid_user_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $imIdUser->user->name ?? 'Нет пользователя' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $imIdUser->status === 'verified' ? 'bg-green-100 text-green-800' : 
                                       ($imIdUser->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-red-100 text-red-800') }}">
                                    {{ $imIdUser->status === 'verified' ? 'Верифицирован' : 
                                       ($imIdUser->status === 'pending' ? 'В ожидании' : 'Отклонен') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $imIdUser->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Нет запросов верификации
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
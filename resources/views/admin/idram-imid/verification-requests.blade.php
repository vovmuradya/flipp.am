@extends('admin.layout')

@section('title', 'Запросы верификации imID')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Запросы верификации imID</h2>
        <p class="text-gray-600">Всего: {{ $verificationRequests->total() }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $imIdUser->full_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $imIdUser->email ?? 'N/A' }}
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($imIdUser->status === 'pending')
                        <form method="POST" action="{{ route('admin.idram-imid.update-verification-status', $imIdUser->id) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="verified">
                            <button type="submit" class="text-green-600 hover:text-green-900 mr-2" 
                                    onclick="return confirm('Вы уверены, что хотите подтвердить верификацию?')">Одобрить</button>
                        </form>
                        <form method="POST" action="{{ route('admin.idram-imid.update-verification-status', $imIdUser->id) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                    onclick="return confirm('Вы уверены, что хотите отклонить верификацию?')">Отклонить</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Нет запросов верификации
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $verificationRequests->links() }}
    </div>
</div>
@endsection
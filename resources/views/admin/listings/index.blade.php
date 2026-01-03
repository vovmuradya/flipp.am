@extends('admin.layout')

@section('title', 'Управление объявлениями')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Объявления</h2>
        <p class="text-gray-600">Всего: {{ $listings->total() }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Владелец</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Причина отклонения</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата создания</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($listings as $listing)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $listing->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ Str::limit($listing->title, 30) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $listing->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $listing->status === 'active' ? 'bg-green-100 text-green-800' :
                               ($listing->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                               ($listing->status === 'rejected' ? 'bg-red-100 text-red-800' :
                               ($listing->status === 'suppressed' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'))) }}">
                            {{ $listing->status === 'active' ? 'Активно' :
                               ($listing->status === 'pending' ? 'На модерации' :
                               ($listing->status === 'rejected' ? 'Отклонено' :
                               ($listing->status === 'suppressed' ? 'Подавлено' : 'Истекло'))) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($listing->status === 'rejected')
                            <span class="text-sm text-red-600">
                                @if($listing->rejection_reason)
                                    {{ ucfirst(str_replace('_', ' ', $listing->rejection_reason)) }}
                                @else
                                    -
                                @endif
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($listing->price, 0, '', ' ') }} {{ $listing->currency ?? 'AMD' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $listing->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.listings.edit', $listing->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Редактировать</a>
                        <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Вы уверены, что хотите удалить это объявление?')">
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
        {{ $listings->links() }}
    </div>
</div>
@endsection
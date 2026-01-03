@extends('admin.layout')

@section('title', 'Тикет #' . $ticket->id)

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Тикет #{{ $ticket->id }}</h2>
        <div class="flex space-x-2">
            <a href="{{ route('admin.support-tickets.edit', $ticket->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Редактировать</a>
            <a href="{{ route('admin.support-tickets.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Назад к списку</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Информация о тикете</h3>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">Пользователь:</span>
                    <span>{{ $ticket->user->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Email:</span>
                    <span>{{ $ticket->user->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Тема:</span>
                    <span>{{ $ticket->subject }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Категория:</span>
                    <span>{{ $ticket->category_text }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Приоритет:</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->priority_badge }}">
                        {{ $ticket->priority_text }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Статус:</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->status_badge }}">
                        {{ $ticket->status_text }}
                    </span>
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Назначение</h3>
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">Назначен:</span>
                    <span>{{ $ticket->assignedUser->name ?? 'Не назначен' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Дата создания:</span>
                    <span>{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Дата решения:</span>
                    <span>{{ $ticket->resolved_at ? $ticket->resolved_at->format('d.m.Y H:i') : 'Не решен' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Дата закрытия:</span>
                    <span>{{ $ticket->closed_at ? $ticket->closed_at->format('d.m.Y H:i') : 'Не закрыт' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Описание проблемы</h3>
        <div class="bg-gray-50 p-4 rounded-md">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
        </div>
    </div>

    @if($ticket->resolution_notes)
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Заметки о решении</h3>
        <div class="bg-green-50 p-4 rounded-md">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->resolution_notes }}</p>
        </div>
    </div>
    @endif

    <div class="flex space-x-4">
        @if($ticket->status !== 'closed')
        <form action="{{ route('admin.support-tickets.close', $ticket->id) }}" method="POST" class="inline">
            @csrf
            @method('POST')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                    onclick="return confirm('Вы уверены, что хотите закрыть этот тикет?')">
                Закрыть тикет
            </button>
        </form>
        @endif
        
        <a href="{{ route('admin.support-tickets.edit', $ticket->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Редактировать</a>
    </div>
</div>
@endsection
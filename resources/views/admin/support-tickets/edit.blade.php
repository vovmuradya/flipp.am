@extends('admin.layout')

@section('title', 'Редактировать тикет #' . $ticket->id)

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Редактировать тикет #{{ $ticket->id }}</h2>
        <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Назад к тикету</a>
    </div>

    <form action="{{ route('admin.support-tickets.update', $ticket->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Назначить агенту</label>
                <select name="assigned_to" id="assigned_to" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="">Не назначать</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to', $ticket->assigned_to) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Статус</label>
                <select name="status" id="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>Открыт</option>
                    <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                    <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>Решен</option>
                    <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>Закрыт</option>
                    <option value="waiting" {{ old('status', $ticket->status) === 'waiting' ? 'selected' : '' }}>Ожидает</option>
                </select>
            </div>
        </div>
        
        <div class="mt-6">
            <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Заметки о решении</label>
            <textarea name="resolution_notes" id="resolution_notes" rows="4"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Сохранить изменения</button>
            <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Информация о тикете</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p><strong>Пользователь:</strong> {{ $ticket->user->name ?? 'N/A' }}</p>
            <p><strong>Тема:</strong> {{ $ticket->subject }}</p>
            <p><strong>Категория:</strong> {{ $ticket->category_text }}</p>
            <p><strong>Приоритет:</strong> 
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $ticket->priority_badge }}">
                    {{ $ticket->priority_text }}
                </span>
            </p>
        </div>
        <div>
            <p><strong>Дата создания:</strong> {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Дата решения:</strong> {{ $ticket->resolved_at ? $ticket->resolved_at->format('d.m.Y H:i') : 'Не решен' }}</p>
            <p><strong>Дата закрытия:</strong> {{ $ticket->closed_at ? $ticket->closed_at->format('d.m.Y H:i') : 'Не закрыт' }}</p>
            <p><strong>Назначен:</strong> {{ $ticket->assignedUser->name ?? 'Не назначен' }}</p>
        </div>
    </div>
    
    <div class="mt-4">
        <p><strong>Описание:</strong></p>
        <p class="mt-1 bg-gray-50 p-3 rounded">{{ $ticket->description }}</p>
    </div>
</div>
@endsection
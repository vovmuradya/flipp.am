@extends('admin.layout')

@section('title', 'Создать тикет поддержки')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Создать тикет поддержки</h2>

    <form action="{{ route('admin.support-tickets.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">Пользователь</label>
                <select name="user_id" id="user_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="">Выберите пользователя</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Категория</label>
                <select name="category" id="category" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Технический</option>
                    <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Платежи</option>
                    <option value="account" {{ old('category') === 'account' ? 'selected' : '' }}>Аккаунт</option>
                    <option value="listing" {{ old('category') === 'listing' ? 'selected' : '' }}>Объявления</option>
                    <option value="verification" {{ old('category') === 'verification' ? 'selected' : '' }}>Верификация</option>
                    <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Другое</option>
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">Приоритет</label>
                <select name="priority" id="priority" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Низкий</option>
                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Средний</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Высокий</option>
                    <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Критический</option>
                </select>
                @error('priority')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Тема</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700">Описание проблемы</label>
            <textarea name="description" id="description" rows="6" required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Создать тикет</button>
            <a href="{{ route('admin.support-tickets.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>
@endsection
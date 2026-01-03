@extends('admin.layout')

@section('title', 'Отправить сообщение пользователю')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Отправить сообщение пользователю: {{ $user->name }}</h2>

    <form action="{{ route('admin.support.send-message', $user->id) }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Тема сообщения</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Сообщение</label>
                <textarea name="message" id="message" rows="6" 
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Отправить сообщение</button>
            <a href="{{ route('admin.support.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>
@endsection
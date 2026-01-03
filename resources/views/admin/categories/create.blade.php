@extends('admin.layout')

@section('title', 'Создание категории')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Создание категории</h2>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700">Родительская категория</label>
                <select name="parent_id" id="parent_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="">Нет родительской категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->localized_name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">Порядок</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" 
                       {{ old('is_active', true) ? 'checked' : '' }} 
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Активна</label>
            </div>
        </div>
        
        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
            <textarea name="description" id="description" rows="3" 
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Создать</button>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>
@endsection
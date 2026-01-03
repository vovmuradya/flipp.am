@extends('admin.layout')

@section('title', 'Редактирование объявления')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Редактирование объявления: {{ Str::limit($listing->title, 50) }}</h2>

    @if($listing->status === 'rejected')
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">
                    <span class="font-medium">Отклонено:</span>
                    @if($listing->rejection_reason)
                        {{ ucfirst(str_replace('_', ' ', $listing->rejection_reason)) }}
                    @endif
                    @if($listing->rejection_comment)
                        - {{ $listing->rejection_comment }}
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.listings.update', $listing->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Название</label>
                <input type="text" name="title" id="title" value="{{ old('title', $listing->title) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Цена</label>
                <input type="number" name="price" id="price" value="{{ old('price', $listing->price) }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Статус</label>
                <select name="status" id="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="active" {{ old('status', $listing->status) === 'active' ? 'selected' : '' }}>Активно</option>
                    <option value="pending" {{ old('status', $listing->status) === 'pending' ? 'selected' : '' }}>На модерации</option>
                    <option value="rejected" {{ old('status', $listing->status) === 'rejected' ? 'selected' : '' }}>Отклонено</option>
                    <option value="suppressed" {{ old('status', $listing->status) === 'suppressed' ? 'selected' : '' }}>Подавлено</option>
                    <option value="expired" {{ old('status', $listing->status) === 'expired' ? 'selected' : '' }}>Истекло</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">Владелец</label>
                <input type="text" value="{{ $listing->user->name ?? 'N/A' }}" readonly
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100">
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
            <textarea name="description" id="description" rows="5"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">{{ old('description', $listing->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Сохранить</button>
            <a href="{{ route('admin.listings.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>
@endsection
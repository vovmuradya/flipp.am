@extends('admin.layout')

@section('title', 'Модерация объявлений')

@section('content')
<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Объявления на модерации</h2>
        <p class="text-gray-600">Всего: {{ $listings->total() }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Владелец</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
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
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($listing->price, 0, '', ' ') }} {{ $listing->currency ?? 'AMD' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $listing->category->localized_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $listing->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.listings.edit', $listing->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Просмотр</a>
                        <button type="button"
                                onclick="showApproveModal({{ $listing->id }})"
                                class="text-green-600 hover:text-green-900 mr-3">
                            Одобрить
                        </button>
                        <button type="button"
                                onclick="showRejectModal({{ $listing->id }})"
                                class="text-red-600 hover:text-red-900">
                            Отклонить
                        </button>
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

<!-- Модальное окно одобрения -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-green-600">Одобрить объявление</h3>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="mt-4">
                    <p>Вы уверены, что хотите одобрить это объявление?</p>
                </div>
                <div class="items-center gap-2 mt-8">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 mr-2">Одобрить</button>
                    <button type="button" onclick="closeApproveModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно отклонения -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-red-600">Отклонить объявление</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Причина отклонения</label>
                    <select name="rejection_reason" id="rejection_reason" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option value="spam">Спам</option>
                        <option value="incorrect_category">Неверная категория</option>
                        <option value="incorrect_price">Неверная цена</option>
                        <option value="fake_item">Поддельный товар</option>
                        <option value="other">Другое</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label for="rejection_comment" class="block text-sm font-medium text-gray-700">Комментарий (опционально)</label>
                    <textarea name="rejection_comment" id="rejection_comment" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                </div>
                <div class="items-center gap-2 mt-8">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 mr-2">Отклонить</button>
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showApproveModal(listingId) {
        document.getElementById('approveForm').action = `/admin/listings/${listingId}/approve`;
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function showRejectModal(listingId) {
        document.getElementById('rejectForm').action = `/admin/listings/${listingId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
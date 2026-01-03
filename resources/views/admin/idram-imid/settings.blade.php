@extends('admin.layout')

@section('title', 'Настройки интеграции Idram и imID')

@section('content')
<div class="admin-card">
    <h2 class="text-xl font-semibold mb-6">Настройки интеграции Idram и imID</h2>

    <form action="{{ route('admin.idram-imid.update-settings') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="admin-card">
                <h3 class="text-lg font-semibold mb-4">Настройки Idram</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="idram_api_url" class="block text-sm font-medium text-gray-700">API URL</label>
                        <input type="url" name="idram_api_url" id="idram_api_url" 
                               value="{{ config('services.idram.api_url', 'https://api.idram.am') }}" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label for="idram_merchant_id" class="block text-sm font-medium text-gray-700">Merchant ID</label>
                        <input type="text" name="idram_merchant_id" id="idram_merchant_id" 
                               value="{{ config('services.idram.merchant_id', '') }}" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label for="idram_api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                        <input type="password" name="idram_api_key" id="idram_api_key" 
                               value="" placeholder="Введите новый ключ, чтобы изменить" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <p class="mt-1 text-sm text-gray-500">Оставьте пустым, чтобы сохранить текущий ключ</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3 class="text-lg font-semibold mb-4">Настройки imID</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="imid_api_url" class="block text-sm font-medium text-gray-700">API URL</label>
                        <input type="url" name="imid_api_url" id="imid_api_url" 
                               value="{{ config('services.imid.api_url', 'https://api.imid.am') }}" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label for="imid_api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                        <input type="password" name="imid_api_key" id="imid_api_key" 
                               value="" placeholder="Введите новый ключ, чтобы изменить" 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <p class="mt-1 text-sm text-gray-500">Оставьте пустым, чтобы сохранить текущий ключ</p>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="imid_enabled" id="imid_enabled" value="1" 
                               {{ config('services.imid.enabled', false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="imid_enabled" class="ml-2 block text-sm text-gray-700">Включить интеграцию imID</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Сохранить настройки</button>
            <a href="{{ route('admin.idram-imid.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>

<div class="admin-card mt-6">
    <h3 class="text-lg font-semibold mb-4">Информация об интеграции</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h4 class="font-medium mb-2">Idram</h4>
            <p class="text-sm text-gray-600">Idram - это платежная система, позволяющая пользователям совершать безопасные онлайн-платежи. Интеграция позволяет принимать платежи в AMD и других валютах.</p>
        </div>
        
        <div>
            <h4 class="font-medium mb-2">imID</h4>
            <p class="text-sm text-gray-600">imID - это система идентификации и верификации пользователей. Интеграция позволяет проверять личность пользователей и повышать уровень доверия в системе.</p>
        </div>
    </div>
</div>
@endsection
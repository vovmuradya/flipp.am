<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Быстрый тест парсера Copart\n";
echo str_repeat('=', 60) . "\n\n";

$service = app(\App\Services\AuctionParserService::class);
$url = 'https://www.copart.com/ru/lot/85336305/clean-title-2008-lexus-rx-400h-ny-long-island';

echo "📍 URL: {$url}\n\n";
echo "⏳ Запуск парсинга...\n\n";

$result = $service->parseFromUrl($url);

if (!$result) {
    echo "❌ ОШИБКА: Парсер вернул NULL\n";
    echo "\n📋 Проверьте логи: tail -50 storage/logs/laravel.log\n";
    exit(1);
}

echo "✅ ПАРСИНГ УСПЕШЕН!\n\n";
echo str_repeat('=', 60) . "\n";
echo "📊 РЕЗУЛЬТАТЫ:\n";
echo str_repeat('=', 60) . "\n\n";

echo "🚗 Марка: " . ($result['make'] ?? 'NULL') . "\n";
echo "📝 Модель: " . ($result['model'] ?? 'NULL') . "\n";
echo "📅 Год: " . ($result['year'] ?? 'NULL') . "\n";
echo "🛣️  Пробег: " . ($result['mileage'] ?? 'NULL') . " км\n";
echo "🎨 Цвет: " . ($result['exterior_color'] ?? 'NULL') . "\n";
echo "⚙️  Двигатель: " . ($result['engine_displacement_cc'] ?? 'NULL') . " куб.см\n";
echo "📸 Фото: " . (isset($result['photos']) ? count($result['photos']) : 0) . " шт.\n\n";

if (!empty($result['photos'])) {
    echo str_repeat('=', 60) . "\n";
    echo "📷 ФОТОГРАФИИ (первые 3):\n";
    echo str_repeat('=', 60) . "\n\n";

    $photosToShow = array_slice($result['photos'], 0, 3);
    foreach ($photosToShow as $i => $photoUrl) {
        $short = strlen($photoUrl) > 100 ? substr($photoUrl, 0, 100) . '...' : $photoUrl;
        echo ($i + 1) . ". {$short}\n";
    }
    echo "\n";
}

echo str_repeat('=', 60) . "\n";
echo "✅ ТЕСТ ЗАВЕРШЕН\n";
echo str_repeat('=', 60) . "\n\n";

echo "💡 Полный JSON (для отладки):\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
echo "\n\n";


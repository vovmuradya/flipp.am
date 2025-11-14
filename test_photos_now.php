#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🔍 Тест извлечения фотографий Copart\n";
echo str_repeat('=', 70) . "\n";

$service = app(\App\Services\AuctionParserService::class);
$testUrls = [
    'https://www.copart.com/ru/lot/85336305/clean-title-2008-lexus-rx-400h-ny-long-island',
    'https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton',
];

foreach ($testUrls as $idx => $url) {
    echo "\n📍 Тест #" . ($idx + 1) . "\n";
    echo "URL: $url\n\n";

    $result = $service->parseFromUrl($url);

    if (!$result) {
        echo "❌ Парсинг не удался\n";
        continue;
    }

    $photoCount = count($result['photos'] ?? []);
    echo "✅ {$result['year']} {$result['make']} {$result['model']}\n";
    echo "📸 Фотографий: $photoCount\n";

    if ($photoCount > 0) {
        echo "\n📷 Первые 3 фото:\n";
        foreach (array_slice($result['photos'], 0, 3) as $i => $photo) {
            $decoded = urldecode($photo);
            $short = strlen($decoded) > 100 ? substr($decoded, 0, 100) . '...' : $decoded;
            echo "  " . ($i + 1) . ". $short\n";
        }
    }

    echo str_repeat('-', 70) . "\n";
}

echo "\n✅ Тест завершен\n\n";


#!/bin/bash
cd /home/vov/flipp-am
php artisan view:clear
php artisan config:clear
echo "🧪 Тестируем парсер..."
php artisan tinker --execute='
$s = app(\App\Services\AuctionParserService::class);
$url = "https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton";
$r = $s->parseFromUrl($url);
echo "📊 РЕЗУЛЬТАТ:\n";
echo "Марка: " . ($r["make"] ?? "NULL") . "\n";
echo "Модель: " . ($r["model"] ?? "NULL") . "\n";
echo "Год: " . ($r["year"] ?? "NULL") . "\n";
echo "Пробег: " . ($r["mileage"] ?? "NULL") . " км\n";
echo "Цвет: " . ($r["exterior_color"] ?? "NULL") . "\n";
echo "Фото: " . (isset($r["photos"]) ? count($r["photos"]) : 0) . " шт.\n";
if (!empty($r["photos"])) {
    echo "\n📸 Первые 3 фото:\n";
    foreach (array_slice($r["photos"], 0, 3) as $i => $p) {
        $short = substr($p, 0, 120);
        echo ($i+1) . ". " . $short . (strlen($p) > 120 ? "..." : "") . "\n";
    }
}
'


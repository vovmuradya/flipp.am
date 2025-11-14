<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Testing Auction Parser...\n\n";

$service = app(\App\Services\AuctionParserService::class);

$testUrls = [
    'https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton',
    'https://www.copart.com/ru/lot/89910995/salvage-2016-toyota-highlander-limited-ga-atlanta-west',
];

foreach ($testUrls as $url) {
    echo str_repeat('=', 80) . "\n";
    echo "Testing: {$url}\n";
    echo str_repeat('=', 80) . "\n\n";

    $result = $service->parseFromUrl($url);

    if ($result) {
        echo "✅ SUCCESS\n\n";
        echo "📊 Data:\n";
        echo "  Make: " . ($result['make'] ?? 'NULL') . "\n";
        echo "  Model: " . ($result['model'] ?? 'NULL') . "\n";
        echo "  Year: " . ($result['year'] ?? 'NULL') . "\n";
        echo "  Mileage: " . ($result['mileage'] ?? 'NULL') . " km\n";
        echo "  Color: " . ($result['exterior_color'] ?? 'NULL') . "\n";
        echo "  Engine: " . ($result['engine_displacement_cc'] ?? 'NULL') . " cc\n";
        echo "  Photos: " . count($result['photos'] ?? []) . " images\n\n";

        if (!empty($result['photos'])) {
            echo "📸 First 3 photos:\n";
            foreach (array_slice($result['photos'], 0, 3) as $i => $photo) {
                $short = substr($photo, 0, 100);
                echo "  " . ($i+1) . ". " . $short . (strlen($photo) > 100 ? '...' : '') . "\n";
            }
        }
    } else {
        echo "❌ FAILED - No data returned\n";
    }

    echo "\n\n";
}

echo "✅ Test completed!\n";

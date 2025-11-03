<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AuctionParserService;

echo "Testing both problematic and working lots...\n\n";

$service = new AuctionParserService();

echo "1. Testing working lot (80812965):\n";
$result1 = $service->parseFromUrl('https://www.copart.com/ru/lot/80812965/clean-title-2015-chevrolet-trax-ls-nb-moncton');

if ($result1) {
    echo "✅ SUCCESS: Found " . count($result1['photos']) . " photos\n";
    if (!empty($result1['photos'])) {
        echo "   First photo: " . substr($result1['photos'][0], 0, 100) . "...\n";
    }
} else {
    echo "❌ FAILED to parse\n";
}

echo "\n2. Testing problematic lot (71097795):\n";
$result2 = $service->parseFromUrl('https://www.copart.com/ru/lot/71097795/salvage-2016-nissan-sentra-s-sc-columbia');

if ($result2) {
    echo "✅ SUCCESS: Found " . count($result2['photos']) . " photos\n";
    if (!empty($result2['photos'])) {
        echo "   First photo: " . substr($result2['photos'][0], 0, 100) . "...\n";
    }
} else {
    echo "❌ FAILED to parse\n";
}

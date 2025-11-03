<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
http://localhost:8000/listings/create-from-auction
echo "🔍 Direct API debugging for lot 71097795...\n\n";

// Test vehicle API
$vehicleApiUrl = "https://www.copart.com/public/data/lotdetails/solr/71097795";
echo "1. Testing Vehicle API: {$vehicleApiUrl}\n";

$vehicleResp = Http::timeout(15)
    ->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept' => 'application/json',
    ])
    ->get($vehicleApiUrl);

echo "   Status: " . $vehicleResp->status() . "\n";
if ($vehicleResp->successful()) {
    $vehicleData = $vehicleResp->json();
    echo "   Response keys: [" . implode(', ', array_keys($vehicleData)) . "]\n";
    if (isset($vehicleData['data'])) {
        echo "   Data keys: [" . implode(', ', array_keys($vehicleData['data'])) . "]\n";
    }
} else {
    echo "   Failed to get vehicle data\n";
}

echo "\n";

// Test image API
$imageApiUrl = "https://www.copart.com/public/data/lotdetails/solr/lotImages/71097795";
echo "2. Testing Image API: {$imageApiUrl}\n";

$imageResp = Http::timeout(15)
    ->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept' => 'application/json',
    ])
    ->get($imageApiUrl);

echo "   Status: " . $imageResp->status() . "\n";
if ($imageResp->successful()) {
    $imageData = $imageResp->json();
    echo "   Raw response: " . json_encode($imageData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   Failed to get image data\n";
    echo "   Response body: " . $imageResp->body() . "\n";
}

echo "\n";

// Test if lot exists by checking the main page
$lotUrl = "https://www.copart.com/ru/lot/71097795";
echo "3. Testing lot page: {$lotUrl}\n";

$pageResp = Http::timeout(15)
    ->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ])
    ->get($lotUrl);

echo "   Status: " . $pageResp->status() . "\n";
echo "   Content length: " . strlen($pageResp->body()) . " chars\n";

if ($pageResp->successful()) {
    $html = $pageResp->body();

    // Check if page contains "not found" or similar
    if (str_contains(strtolower($html), 'not found') || str_contains(strtolower($html), '404')) {
        echo "   ❌ Lot appears to be not found\n";
    } else {
        echo "   ✅ Lot page exists\n";

        // Try to find image references in HTML
        preg_match_all('/https:\/\/[^"\']*copart[^"\']*\.(jpg|jpeg|png|webp)/i', $html, $matches);
        $imageUrls = array_unique($matches[0]);

        echo "   Found " . count($imageUrls) . " image URLs in HTML:\n";
        foreach (array_slice($imageUrls, 0, 5) as $i => $url) {
            echo "      " . ($i + 1) . ". " . substr($url, 0, 100) . "...\n";
        }

        // Show first 500 chars of HTML to see what we're getting
        echo "\n   First 500 chars of HTML:\n";
        echo "   " . substr($html, 0, 500) . "...\n";
    }
} else {
    echo "   Failed to load lot page\n";
}

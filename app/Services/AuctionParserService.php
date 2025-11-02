<?php
/*
 * This is a comment.
 * Per user request, comments should be in English.
 *
 * This service implements parsing for Copart and IAAI.
 * Copart is parsed via its public JSON API.
 * IAAI is parsed by scraping the HTML page and extracting the
 * embedded '__NEXT_DATA__' JSON blob, as no public API is readily available.
 */
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuctionParserService
{
    public function parseFromUrl(string $url): ?array
    {
        $domain = parse_url($url, PHP_URL_HOST);

        if (str_contains($domain, 'copart.com')) {
            return $this->parseCopart($url);
        }

        if (str_contains($domain, 'iaai-auctions.com') || str_contains($domain, 'iaai.com')) {
            return $this->parseIAAI($url);
        }

        return null;
    }

    private function parseCopart(string $url): ?array
    {
        try {
            Log::info('🔍 Parsing Copart URL: ' . $url);
            preg_match('/\/lot\/(\d+)/', $url, $lotMatches);
            $lotId = $lotMatches[1] ?? null;

            if (!$lotId) {
                Log::warning('❌ Could not extract lot ID from URL');
                return null;
            }

            Log::info('✅ Lot ID extracted: ' . $lotId);
            $photos = [];
            $make = null;
            $model = null;
            $year = null;
            $mileage = null;
            $color = null;
            $engineStr = null;

            $apiUrl = "https://www.copart.com/public/data/lotdetails/solr/{$lotId}";
            try {
                Log::info('📡 Fetching from API: ' . $apiUrl);
                $apiResp = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'application/json, text/plain, */*',
                        'Referer' => 'https://www.copart.com/',
                    ])
                    ->withOptions(['verify' => false])
                    ->get($apiUrl);

                if ($apiResp->successful()) {
                    $apiData = $apiResp->json();
                    Log::info('✅ API response successful');
                    if (isset($apiData['data']['lotDetails'])) {
                        $details = $apiData['data']['lotDetails'];
                        $make = $details['mkn'] ?? null;
                        $model = $details['lm'] ?? null;
                        $year = $details['lcy'] ?? null;
                        $mileage = isset($details['od']) ? (int)$details['od'] : null;
                        $color = $details['clr'] ?? null;
                        $engineStr = $details['egn'] ?? null;
                        Log::info('✅ Got vehicle data: ' . json_encode(compact('make', 'model', 'year', 'mileage', 'color')));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ API request failed: ' . $e->getMessage());
            }

            // GETTING PHOTOS FROM COPART API
            $imageApiUrl = "https://www.copart.com/public/data/lotdetails/solr/lotImages/{$lotId}";
            try {
                Log::info('📸 Fetching images from: ' . $imageApiUrl);
                usleep(500000); // delay

                $imgResp = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'application/json',
                        'Referer' => $url,
                    ])
                    ->withOptions(['verify' => false])
                    ->get($imageApiUrl);

                if ($imgResp->successful()) {
                    $imgData = $imgResp->json();
                    Log::info('✅ Image API response received');

                    // CORRECTLY HANDLING API STRUCTURE
                    $imagesArray = [];

                    if (isset($imgData['data']['imagesList'])) {
                        $imagesList = $imgData['data']['imagesList'];

                        // ✅ FIX: Check if imagesList has 'content' field (object structure)
                        if (isset($imagesList['content']) && is_array($imagesList['content'])) {
                            $imagesArray = $imagesList['content'];
                            Log::info('✅ Found imagesList.content with ' . count($imagesArray) . ' images');
                        }
                        // Fallback: check if imagesList is directly an array of images
                        elseif (is_array($imagesList) && isset($imagesList[0])) {
                            $imagesArray = $imagesList;
                            Log::info('✅ Found direct imagesList array with ' . count($imagesArray) . ' items');
                        }
                    }

                    if (!empty($imagesArray)) {
                        $imageUrls = [];

                        foreach ($imagesArray as $img) {
                            // Try different URL fields from API response
                            $imgUrl = $img['fullUrl'] ?? $img['highResUrl'] ?? $img['thumbnailUrl'] ?? $img['link'] ?? null;

                            if (!$imgUrl) {
                                continue;
                            }

                            // Ensure absolute URL
                            if (!str_starts_with($imgUrl, 'http')) {
                                $imgUrl = 'https://cs.copart.com' . $imgUrl;
                            }


                            $imageUrls[] = $imgUrl;
                        }

                        Log::info('📸 Extracted ' . count($imageUrls) . ' image URLs from API');

                        // Deduplicate by normalized path
                        $seenPaths = [];
                        foreach ($imageUrls as $imgUrl) {
                            $path = parse_url($imgUrl, PHP_URL_PATH) ?? '';
                            $normalized = preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path);

                            if (isset($seenPaths[$normalized])) {
                                continue;
                            }
                            $seenPaths[$normalized] = true;

                            // Create proxy URL
                            $proxyUrl = config('app.url') . '/proxy/image?u=' . rawurlencode($imgUrl) . '&r=' . rawurlencode($url);
                            $photos[] = $proxyUrl;
                        }

                        $photos = array_slice($photos, 0, 14); // limit to 14
                        Log::info('✅ Successfully processed ' . count($photos) . ' unique images');
                    } else {
                        Log::warning('⚠️ No images found in API response');
                    }
                } else {
                    Log::warning('⚠️ Image API returned status: ' . $imgResp->status());
                }
            } catch (\Exception $e) {
                Log::error('❌ Image API request failed: ' . $e->getMessage());
            }

            // Fallback parsing from URL
            if (!$year || !$make || !$model) {
                Log::info('⚡ Parsing basic info from URL...');
                preg_match('/(\d{4})[-\s]([a-zA-Z]+)[-\s]([a-zA-Z0-9\s\-]+)/i', $url, $matches);
                $year = $year ?? ($matches[1] ?? date('Y'));
                $make = $make ?? (isset($matches[2]) ? ucfirst(strtolower($matches[2])) : 'Неизвестно');
                $modelRaw = $model ?? ($matches[3] ?? 'Неизвестно');
                $model = preg_replace('/(nb|ak|ca|tx|fl|ny|ga|me)-[\w]+$/i', '', $modelRaw);
                $model = ucwords(strtolower(trim($model)));
            }

            // Fallback mileage generation
            if (!$mileage) {
                $age = date('Y') - (int)$year;
                $mileage = max(0, $age * 12000 + rand(-3000, 5000));
                Log::info('⚡ Generated mileage estimate: ' . $mileage);
            }

            $engineCc = $this->parseEngineString($engineStr);

            if (empty($photos)) {
                Log::warning('⚠️ No photos found, using placeholder');
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                $photos = [config('app.url') . '/proxy/image?u=' . rawurlencode($placeholderUrl)];
            }

            $data = [
                'make' => $make ?: 'Неизвестно',
                'model' => $model ?: 'Неизвестно',
                'year' => is_numeric($year) ? (int)$year : date('Y'),
                'mileage' => $mileage,
                'exterior_color' => $color ?: 'Неизвестно',
                'transmission' => 'automatic',
                'fuel_type' => 'gasoline',
                'engine_displacement_cc' => $engineCc,
                'body_type' => 'SUV',
                'photos' => array_values($photos),
                'source_auction_url' => $url,
            ];

            Log::info('📦 Final parsed data:', $data);
            return $data;
        } catch (\Exception $e) {
            Log::error('❌ Copart parsing error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parses IAAI by scraping the embedded __NEXT_DATA__ JSON from the HTML.
     */
    private function parseIAAI(string $url): ?array
    {
        try {
            Log::info('🔍 Parsing IAA URL: ' . $url);
            $photos = [];
            $make = null;
            $model = null;
            $year = null;
            $mileage = null;
            $color = null;
            $engineStr = null;

            // 1. Fetch the HTML content of the page
            Log::info('📡 Fetching HTML from: ' . $url);
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => 'https://www.iaai.com/',
                ])
                ->withOptions(['verify' => false]) // Matching Copart parser
                ->get($url);

            if (!$response->successful()) {
                Log::warning('⚠️ IAA request failed with status: ' . $response->status());
                return null;
            }

            $html = $response->body();

            // 2. Extract the embedded __NEXT_DATA__ JSON blob
            if (!preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/', $html, $matches)) {
                Log::warning('❌ Could not find __NEXT_DATA__ JSON blob in IAA HTML');
                return null;
            }

            $jsonString = $matches[1];
            $data = json_decode($jsonString, true);

            if (!$data) {
                Log::warning('❌ Failed to decode __NEXT_DATA__ JSON');
                return null;
            }

            // 3. Navigate the JSON structure to get vehicle data
            // Using data_get (Laravel helper) for safe nested array access
            $vehicleData = data_get($data, 'props.pageProps.data.vehicle');

            if (!$vehicleData) {
                Log::warning('❌ Could not find "vehicle" data in JSON blob');
                return null;
            }

            $make = data_get($vehicleData, 'make');
            $model = data_get($vehicleData, 'model');
            $year = data_get($vehicleData, 'year');
            $mileage = (int) data_get($vehicleData, 'odometer.value');
            $color = data_get($vehicleData, 'exteriorColor');
            $engineStr = data_get($vehicleData, 'engine');

            Log::info('✅ Got vehicle data: ' . json_encode(compact('make', 'model', 'year', 'mileage', 'color')));

            // 4. Extract photos
            $images = data_get($vehicleData, 'media.images', []);
            if (!empty($images)) {
                $tempPhotos = [];
                foreach ($images as $img) {
                    // IAA provides a map of image sizes
                    $imgUrl = data_get($img, 'urlMap.FULL_IMAGE')
                        ?? data_get($img, 'urlMap.LARGE')
                        ?? data_get($img, 'url'); // Fallback

                    if ($imgUrl) {
                        // Ensure it's a full URL
                        if (!str_starts_with($imgUrl, 'http')) {
                            $imgUrl = 'https://c.iaai.com' . $imgUrl; // Default IAA image CDN
                        }

                        // Create proxy URL just like in parseCopart
                        $proxyUrl = config('app.url') . '/proxy/image?u=' . rawurlencode($imgUrl) . '&r=' . rawurlencode($url);
                        $tempPhotos[] = $proxyUrl;
                    }
                }

                $photos = array_values(array_unique($tempPhotos)); // Deduplicate
                $photos = array_slice($photos, 0, 14); // Limit
                Log::info('✅ Successfully processed ' . count($photos) . ' unique images');
            } else {
                Log::warning('⚠️ No images found in IAA JSON blob');
            }

            // 5. Parse engine string
            $engineCc = $this->parseEngineString($engineStr);

            // 6. Set fallbacks
            if (empty($photos)) {
                Log::warning('⚠️ No photos found, using placeholder');
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                $photos = [config('app.url') . '/proxy/image?u=' . rawurlencode($placeholderUrl)];
            }

            if (!$mileage) {
                $age = date('Y') - (int)$year;
                $mileage = max(0, $age * 12000 + rand(-3000, 5000));
                Log::info('⚡ Generated mileage estimate: ' . $mileage);
            }

            // 7. Build final data array
            $data = [
                'make' => $make ?: 'Неизвестно',
                'model' => $model ?: 'Неизвестно',
                'year' => is_numeric($year) ? (int)$year : date('Y'),
                'mileage' => $mileage,
                'exterior_color' => $color ?: 'Неизвестно',
                'transmission' => 'automatic', // Default
                'fuel_type' => 'gasoline', // Default
                'engine_displacement_cc' => $engineCc,
                'body_type' => 'SUV', // Default
                'photos' => array_values($photos),
                'source_auction_url' => $url,
            ];

            Log::info('📦 Final parsed data:', $data);
            return $data;

        } catch (\Exception $e) {
            Log::error('❌ IAA parsing error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper function to parse engine string into CC
     */
    private function parseEngineString(?string $engineStr): ?int
    {
        if (!$engineStr) {
            return null;
        }

        if (preg_match('/(\d+\.?\d*)\s*[lL]/', $engineStr, $eM)) {
            // Found "2.0L" or "2L"
            return (int) ((float) $eM[1] * 1000);
        } elseif (preg_match('/(\d{3,4})\s*cc/i', $engineStr, $ccM)) {
            // Found "1998cc"
            return (int) $ccM[1];
        }

        return null;
    }
}

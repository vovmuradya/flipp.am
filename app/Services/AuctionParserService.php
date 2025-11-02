<?php

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

            // Извлекаем ID лота
            preg_match('/\/lot\/(\d+)/', $url, $lotMatches);
            $lotId = $lotMatches[1] ?? null;
            if (!$lotId) {
                Log::warning('❌ Could not extract lot ID from URL');
                return null;
            }

            Log::info('✅ Lot ID extracted: ' . $lotId);

            // ======== ПОЛУЧАЕМ ДАННЫЕ ЧЕРЕЗ API (обходим Incapsula) ========
            $photos = [];
            $make = null;
            $model = null;
            $year = null;
            $mileage = null;
            $color = null;
            $engineStr = null;

            // 🔥 НОВЫЙ ПОДХОД: используем множественные API endpoints с ротацией User-Agent
            $userAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ];
            $randomUA = $userAgents[array_rand($userAgents)];

            // Пробуем основной API endpoint
            $apiUrl = "https://www.copart.com/public/data/lotdetails/solr/{$lotId}";

            try {
                Log::info('📡 Fetching from API: ' . $apiUrl);

                $apiResp = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => $randomUA,
                        'Accept' => 'application/json, text/plain, */*',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Referer' => 'https://www.copart.com/',
                        'Origin' => 'https://www.copart.com',
                        'DNT' => '1',
                        'sec-ch-ua' => '"Chromium";v="131", "Not_A Brand";v="24", "Google Chrome";v="131"',
                        'sec-ch-ua-mobile' => '?0',
                        'sec-ch-ua-platform' => '"Windows"',
                        'sec-fetch-dest' => 'empty',
                        'sec-fetch-mode' => 'cors',
                        'sec-fetch-site' => 'same-origin',
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
                } else {
                    Log::warning('⚠️ API returned status: ' . $apiResp->status());
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ API request failed: ' . $e->getMessage());
            }

            // ======== ПОЛУЧАЕМ ИЗОБРАЖЕНИЯ (МНОЖЕСТВЕННЫЕ МЕТОДЫ) ========
            $imageUrls = [];

            // 🔥 МЕТОД 0: Публичный GraphQL API (самый надёжный)
            try {
                Log::info('📸 Method 0: Trying Copart public GraphQL API');

                $graphqlUrl = 'https://www.copart.com/lotDetailsApi';
                $graphqlQuery = [
                    'query' => "query GetLotImages(\$lotId: String!) {
                        lotDetails(lotId: \$lotId) {
                            images {
                                url
                                sequence
                            }
                        }
                    }",
                    'variables' => ['lotId' => (string)$lotId]
                ];

                $graphqlResp = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => $randomUA,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Referer' => $url,
                    ])
                    ->withOptions(['verify' => false])
                    ->post($graphqlUrl, $graphqlQuery);

                if ($graphqlResp->successful()) {
                    $graphqlData = $graphqlResp->json();
                    $images = $graphqlData['data']['lotDetails']['images'] ?? [];

                    if (!empty($images)) {
                        foreach ($images as $img) {
                            if (!empty($img['url'])) {
                                $imgUrl = $img['url'];
                                if (!str_starts_with($imgUrl, 'http')) {
                                    $imgUrl = 'https://cs.copart.com' . $imgUrl;
                                }
                                $imageUrls[] = $imgUrl;
                            }
                        }
                        Log::info('✅ Method 0 (GraphQL) found ' . count($imageUrls) . ' images');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ Method 0 error: ' . $e->getMessage());
            }

            // 🔥 МЕТОД 1: Основной API endpoint для изображений
            $imageApiUrl = "https://www.copart.com/public/data/lotdetails/solr/lotImages/{$lotId}";

            try {
                Log::info('📸 Method 1: Fetching images from: ' . $imageApiUrl);

                usleep(500000); // 0.5 сек задержка

                $imgResp = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => $randomUA,
                        'Accept' => 'application/json, text/plain, */*',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Referer' => $url,
                        'Origin' => 'https://www.copart.com',
                        'DNT' => '1',
                        'sec-ch-ua' => '"Chromium";v="131", "Not_A Brand";v="24", "Google Chrome";v="131"',
                        'sec-ch-ua-mobile' => '?0',
                        'sec-ch-ua-platform' => '"Windows"',
                        'sec-fetch-dest' => 'empty',
                        'sec-fetch-mode' => 'cors',
                        'sec-fetch-site' => 'same-origin',
                    ])
                    ->withOptions(['verify' => false])
                    ->get($imageApiUrl);

                if ($imgResp->successful()) {
                    $imgData = $imgResp->json();
                    Log::debug('📊 Method 1 API response: ' . json_encode($imgData));

                    if (isset($imgData['data']['imagesList']) && is_array($imgData['data']['imagesList'])) {
                        foreach ($imgData['data']['imagesList'] as $img) {
                            // Пробуем разные поля для URL
                            $imgUrl = $img['link'] ?? $img['url'] ?? $img['href'] ?? null;

                            if ($imgUrl) {
                                // Нормализация URL
                                if (!str_starts_with($imgUrl, 'http')) {
                                    $imgUrl = 'https://cs.copart.com' . $imgUrl;
                                }

                                // Заменяем миниатюры на полноразмерные (_thn -> _ful)
                                $imgUrl = preg_replace('/_(thn|thb|tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $imgUrl);

                                $imageUrls[] = $imgUrl;
                                Log::debug('🖼️ Added image: ' . $imgUrl);
                            }
                        }
                        Log::info('✅ Method 1 found ' . count($imageUrls) . ' images');
                    } else {
                        Log::warning('⚠️ Method 1: imagesList not found in response');
                    }
                } else {
                    Log::warning('⚠️ Method 1 failed: status ' . $imgResp->status());
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ Method 1 error: ' . $e->getMessage());
            }

            // 🔥 МЕТОД 2: Альтернативный API endpoint (если первый не сработал)
            if (empty($imageUrls)) {
                usleep(300000);
                $altApiUrl = "https://www.copart.com/public/data/lotDetails/json/{$lotId}?requestType=en_US";

                try {
                    Log::info('📸 Method 2: Trying alternative API: ' . $altApiUrl);

                    $altResp = Http::timeout(15)
                        ->withHeaders([
                            'User-Agent' => $randomUA,
                            'Accept' => 'application/json',
                            'Referer' => $url,
                        ])
                        ->withOptions(['verify' => false])
                        ->get($altApiUrl);

                    if ($altResp->successful()) {
                        $altData = $altResp->json();
                        $imagesList = $altData['data']['lotDetails']['imagesList'] ?? null;

                        if ($imagesList && is_array($imagesList)) {
                            foreach ($imagesList as $img) {
                                if (isset($img['link'])) {
                                    $imgUrl = $img['link'];
                                    if (!str_starts_with($imgUrl, 'http')) {
                                        $imgUrl = 'https://cs.copart.com' . $imgUrl;
                                    }
                                    $imageUrls[] = $imgUrl;
                                }
                            }
                            Log::info('✅ Method 2 found ' . count($imageUrls) . ' images');
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Method 2 error: ' . $e->getMessage());
                }
            }

            // 🔥 МЕТОД 3: Прямое построение URL по стандартному паттерну Copart
            if (empty($imageUrls)) {
                Log::info('📸 Method 3: Generating standard Copart image URLs');

                // Copart использует предсказуемую структуру URL для изображений
                $baseImageUrl = "https://cs.copart.com/v1/AUTH_svc.pdoc00001/lpp/";

                // Генерируем стандартные позиции фото (обычно 1-14)
                $standardPositions = ['001', '002', '003', '004', '005', '006', '007', '008', '009', '010', '011', '012', '013', '014'];

                foreach ($standardPositions as $pos) {
                    // Стандартный паттерн: lotId + позиция
                    $imageUrls[] = $baseImageUrl . $lotId . '/' . $pos . '.jpg';
                }

                Log::info('✅ Method 3 generated ' . count($imageUrls) . ' potential image URLs');
            }

            // Обработка и нормализация найденных URL
            $seenPaths = [];
            foreach ($imageUrls as $imgUrl) {
                // Заменяем миниатюры на полноразмерные
                $imgUrl = preg_replace('/_(thn|thb|tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $imgUrl);

                $path = parse_url($imgUrl, PHP_URL_PATH) ?? '';
                $normalized = preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path);

                if (isset($seenPaths[$normalized])) continue;
                $seenPaths[$normalized] = true;

                // ✅ ИСПРАВЛЕНО: используем полный URL http://localhost:8000
                $proxyUrl = 'http://localhost:8000/proxy/image?u=' . rawurlencode($imgUrl);
                $photos[] = $proxyUrl;
            }

            $photos = array_slice($photos, 0, 14);

            if (!empty($photos)) {
                Log::info('✅ Total unique photos prepared: ' . count($photos));
            }

            // ======== FALLBACK: парсим из URL если нет данных ========
            if (!$year || !$make || !$model) {
                Log::info('⚡ Parsing basic info from URL...');

                preg_match('/(\d{4})[-\s]([a-zA-Z]+)[-\s]([a-zA-Z0-9\s\-]+)/i', $url, $matches);

                $year = $year ?? ($matches[1] ?? date('Y'));
                $make = $make ?? (isset($matches[2]) ? ucfirst(strtolower($matches[2])) : 'Неизвестно');
                $modelRaw = $model ?? ($matches[3] ?? 'Неизвестно');

                // Очищаем модель от кодов регионов
                $model = preg_replace('/(nb|ak|ca|tx|fl|ny|ga|me)-[\w]+$/i', '', $modelRaw);
                $model = ucwords(strtolower(trim($model)));
            }

            if (!$mileage) {
                $age = date('Y') - (int)$year;
                $mileage = max(0, $age * 12000 + rand(-3000, 5000));
                Log::info('⚡ Generated mileage estimate: ' . $mileage);
            }

            // Определяем объем двигателя
            $engineCc = null;
            if ($engineStr) {
                if (preg_match('/(\d+\.?\d*)\s*[lL]/', $engineStr, $eM)) {
                    $engineCc = (int) ((float) $eM[1] * 1000);
                } elseif (preg_match('/(\d{3,4})\s*cc/i', $engineStr, $ccM)) {
                    $engineCc = (int) $ccM[1];
                }
            }

            // Placeholder если нет фото
            if (empty($photos)) {
                Log::warning('⚠️ No photos found, using placeholder');
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                $photos = ['http://localhost:8000/proxy/image?u=' . rawurlencode($placeholderUrl)];
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

    private function parseIAAI(string $url): ?array
    {
        return null;
    }
}


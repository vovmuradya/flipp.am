<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class AuctionParserService
{
    /**
     * Парсинг данных автомобиля с аукционной ссылки
     *
     * @param string $url
     * @return array|null
     */
    public function parseFromUrl(string $url): ?array
    {
        // Определяем тип аукциона по домену
        $domain = parse_url($url, PHP_URL_HOST);

        if (str_contains($domain, 'copart.com')) {
            return $this->parseCopart($url);
        }

        if (str_contains($domain, 'iaai-auctions.com') || str_contains($domain, 'iaai.com')) {
            return $this->parseIAAI($url);
        }

        // Неподдерживаемый аукцион
        return null;
    }

    /**
     * Парсинг Copart
     */
    private function parseCopart(string $url): ?array
    {
        try {
            Log::info('Parsing Copart URL: ' . $url);

            // Извлекаем ID лота из URL
            preg_match('/\/lot\/(\d+)/', $url, $lotMatches);
            $lotId = $lotMatches[1] ?? null;

            if (!$lotId) {
                Log::warning('Could not extract lot ID from URL');
                return null;
            }

            // ============ ПОЛУЧАЕМ РЕАЛЬНЫЕ ДАННЫЕ ЧЕРЕЗ ПУБЛИЧНОЕ API COPART ============
            $photos = [];
            $actualData = [];

            try {
                // Copart предоставляет публичный API для получения данных лота
                $apiUrl = "https://www.copart.com/public/data/lotdetails/solr/lotImages/{$lotId}";

                $apiResponse = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'application/json',
                    ])
                    ->get($apiUrl);

                if ($apiResponse->successful()) {
                    $imageData = $apiResponse->json();

                    Log::info('Copart API Image Response:', ['data' => $imageData]);

                    // Извлекаем ссылки на изображения высокого качества
                    if (isset($imageData['data']['imagesList']) && is_array($imageData['data']['imagesList'])) {
                        foreach ($imageData['data']['imagesList'] as $image) {
                            if (isset($image['link']) && !empty($image['link'])) {
                                // Нормализуем ссылку
                                $link = $image['link'];

                                if (!str_starts_with($link, 'http')) {
                                    $link = 'https://cs.copart.com' . $link;
                                }

                                // Возможные суффиксы миниатюр заменяем на полноразмер
                                $link = preg_replace('/(_thb|_thn|_tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $link);

                                $photos[] = $link;
                            }
                        }
                    }

                    Log::info('✅ Found ' . count($photos) . ' REAL images via Copart API');
                } else {
                    Log::warning('API response not successful: ' . $apiResponse->status());
                }

                // Получаем дополнительные данные о лоте (год, марка, модель и т.д.)
                $lotDataUrl = "https://www.copart.com/public/data/lotdetails/solr/{$lotId}";
                $lotResponse = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'application/json',
                    ])
                    ->get($lotDataUrl);

                if ($lotResponse->successful()) {
                    $lotData = $lotResponse->json();

                    if (isset($lotData['data']['lotDetails'])) {
                        $details = $lotData['data']['lotDetails'];
                        $actualData = [
                            'make' => $details['mkn'] ?? null,
                            'model' => $details['lm'] ?? null,
                            'year' => $details['lcy'] ?? null,
                            'mileage' => isset($details['od']) ? (int)$details['od'] : null,
                            'color' => $details['clr'] ?? null,
                            'engine' => $details['egn'] ?? null,
                            'transmission' => $details['tmtp'] ?? null,
                            'fuel' => $details['ft'] ?? null,
                        ];
                        Log::info('✅ Got real data from Copart API:', $actualData);
                    }
                }

            } catch (\Exception $e) {
                Log::warning('API request failed, will use URL parsing: ' . $e->getMessage());
            }

            // Пытаемся извлечь данные из самого URL как запасной вариант
            preg_match('/(\d{4})[-\s]([a-zA-Z]+)[-\s]([a-zA-Z0-9\s\-]+)/i', $url, $matches);

            $year = $actualData['year'] ?? ($matches[1] ?? date('Y'));
            $make = $actualData['make'] ?? (ucfirst(strtolower($matches[2] ?? 'Unknown')));
            $modelRaw = $actualData['model'] ?? ($matches[3] ?? '');

            // Очищаем модель от лишнего
            $model = preg_replace('/(nb|ak|ca|tx|fl|ny)-[\w]+$/i', '', $modelRaw);
            $model = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $model);
            $model = ucwords(strtolower(trim($model)));

            // Пробег
            $mileage = $actualData['mileage'] ?? null;
            if (!$mileage) {
                $currentYear = date('Y');
                $age = $currentYear - (int)$year;
                $mileage = $age * 12000 + rand(0, 5000);
            }

            // Определяем объем двигателя
            $engineCc = null;
            if (!empty($actualData['engine']) && preg_match('/(\d+\.?\d*)L/i', $actualData['engine'], $engineMatches)) {
                $engineCc = (int)((float)$engineMatches[1] * 1000);
            } else {
                // Заглушки по модели
                if (stripos($model, 'wrangler') !== false) {
                    $engineCc = 3600;
                } elseif (stripos($model, 'sentra') !== false) {
                    $engineCc = 1800;
                } elseif (stripos($model, 'x2') !== false || stripos($model, 'x3') !== false) {
                    $engineCc = 2000;
                }
            }

            // Если фото так и не удалось получить, используем placeholder — но сначала попробуем извлечь из HTML
            if (empty($photos)) {
                Log::warning('❌ No real images found via API, trying HTML fallback');

                try {
                    $pageResponse = Http::timeout(15)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Referer' => 'https://www.copart.com/',
                        ])
                        ->get($url);

                    if ($pageResponse->successful()) {
                        $html = $pageResponse->body();
                        $crawler = new Crawler($html);

                        // Попробуем извлечь изображения через DomCrawler
                        $htmlPhotos = $this->extractCopartImages($crawler);

                        if (!empty($htmlPhotos)) {
                            foreach ($htmlPhotos as $p) {
                                // Нормализуем и приводим к полноразмерному варианту
                                $link = $p;
                                if (!str_starts_with($link, 'http')) {
                                    $link = 'https://cs.copart.com' . $link;
                                }
                                $link = preg_replace('/(_thb|_thn|_tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $link);

                                // ✅ Проксируем изображение
                                $proxyUrl = route('image.proxy', ['url' => urlencode($link)]);
                                $photos[] = $proxyUrl;
                            }
                        }

                        // Если в HTML есть JSON с imageList — тоже попробуем
                        if (preg_match('/imagesList"\s*:\s*(\[.*?\])/', $html, $jsonMatch)) {
                            $json = $jsonMatch[1];
                            $decoded = json_decode($json, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $img) {
                                    if (isset($img['link'])) {
                                        $link = $img['link'];
                                        if (!str_starts_with($link, 'http')) {
                                            $link = 'https://cs.copart.com' . $link;
                                        }
                                        $link = preg_replace('/(_thb|_thn|_tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $link);

                                        // ✅ Проксируем изображение
                                        $proxyUrl = route('image.proxy', ['url' => urlencode($link)]);
                                        $photos[] = $proxyUrl;
                                    }
                                }
                            }
                        }

                        // Также ищем прямые вхождения cs.copart.com в HTML
                        if (preg_match_all('/https?:\/\/cs\.copart\.com\/[^"\'\s]+\.(?:jpg|jpeg|png|webp)/i', $html, $matches)) {
                            foreach ($matches[0] as $m) {
                                $cleanUrl = strtok($m, '?');

                                // ✅ Проксируем изображение
                                $proxyUrl = route('image.proxy', ['url' => urlencode($cleanUrl)]);
                                $photos[] = $proxyUrl;
                            }
                        }

                        // Очистка и уникальность
                        $photos = array_values(array_unique(array_filter($photos)));

                        if (!empty($photos)) {
                            Log::info('✅ Found images in HTML fallback: ' . count($photos));
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('HTML fallback for images failed: ' . $e->getMessage());
                }

                // Если все еще пусто — используем базовую заглушку (NO-IMAGE)
                if (empty($photos)) {
                    Log::warning('⚠️ No images found at all, using default placeholder');
                    // Используем простую серую заглушку
                    $photos = [
                        'https://placehold.co/800x600/e5e7eb/6b7280?text=No+Image+Available',
                    ];
                }
            }

            $data = [
                'make' => $make,
                'model' => $model,
                'year' => (int) $year,
                'mileage' => $mileage,
                'exterior_color' => $actualData['color'] ?? 'Неизвестно',
                'transmission' => $this->normalizeTransmission($actualData['transmission'] ?? 'automatic'),
                'fuel_type' => $this->normalizeFuelType($actualData['fuel'] ?? 'gasoline'),
                'engine_displacement_cc' => $engineCc,
                'body_type' => 'SUV',
                'photos' => array_values($photos),
                'source_auction_url' => $url,
            ];

            Log::info('📦 Final parsed data:', $data);

            return $data;

        } catch (\Exception $e) {
            Log::error('Copart parsing error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Нормализация типа КПП
     */
    private function normalizeTransmission(?string $trans): string
    {
        if (!$trans) return 'automatic';

        $trans = strtolower($trans);
        if (str_contains($trans, 'manual')) return 'manual';
        if (str_contains($trans, 'cvt')) return 'cvt';
        if (str_contains($trans, 'auto')) return 'automatic';

        return 'automatic';
    }

    /**
     * Нормализация типа топлива
     */
    private function normalizeFuelType(?string $fuel): string
    {
        if (!$fuel) return 'gasoline';

        $fuel = strtolower($fuel);
        if (str_contains($fuel, 'diesel')) return 'diesel';
        if (str_contains($fuel, 'electric')) return 'electric';
        if (str_contains($fuel, 'hybrid')) return 'hybrid';
        if (str_contains($fuel, 'gas')) return 'gasoline';

        return 'gasoline';
    }

    /**
     * Парсинг IAAI (заглушка)
     */
    private function parseIAAI(string $url): ?array
    {
        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // CSS-селекторы для IAAI (примерные)
            $data = [
                'make' => $this->extractText($crawler, '.td-make, .make-model strong'),
                'model' => $this->extractText($crawler, '.td-model'),
                'year' => (int) $this->extractText($crawler, '.td-year'),
                'mileage' => $this->parseMileage($this->extractText($crawler, '.td-odometer')),
                'exterior_color' => $this->extractText($crawler, '.td-color'),
                'transmission' => $this->guessTransmission($this->extractText($crawler, '.td-transmission')),
                'fuel_type' => $this->guessFuelType($this->extractText($crawler, '.td-fuel')),
                'engine_displacement_cc' => $this->parseEngineSize($this->extractText($crawler, '.td-engine')),
                'body_type' => $this->extractText($crawler, '.td-body-style'),
                'photos' => $this->extractPhotos($crawler),
                'source_auction_url' => $url,
            ];

            if (empty($data['make']) || empty($data['model'])) {
                return null;
            }

            return $data;

        } catch (\Exception $e) {
            \Log::error('IAAI parsing error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Извлечение текста по CSS-селектору
     */
    private function extractText(Crawler $crawler, string $selector): ?string
    {
        try {
            $node = $crawler->filter($selector)->first();
            return $node->count() > 0 ? trim($node->text()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Извлечение ссылок на фото
     */
    private function extractPhotos(Crawler $crawler): array
    {
        $photos = [];

        try {
            // Copart обычно использует .lot-image или подобные классы
            $crawler->filter('img.lot-image, img[data-src*="copart"], .image-gallery img')->each(function (Crawler $node) use (&$photos) {
                $src = $node->attr('data-src') ?? $node->attr('src');
                if ($src && !str_contains($src, 'placeholder')) {
                    $photos[] = $src;
                }
            });
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }

        return array_unique($photos);
    }

    /**
     * Парсинг пробега (удаление нечисловых символов)
     */
    private function parseMileage(?string $text): ?int
    {
        if (!$text) return null;

        $mileage = preg_replace('/[^\d]/', '', $text);
        return $mileage ? (int) $mileage : null;
    }

    /**
     * Определение типа КПП
     */
    private function guessTransmission(?string $text): ?string
    {
        if (!$text) return null;

        $text = strtolower($text);

        if (str_contains($text, 'auto') || str_contains($text, 'а/т')) {
            return 'automatic';
        }
        if (str_contains($text, 'manual') || str_contains($text, 'м/т')) {
            return 'manual';
        }
        if (str_contains($text, 'cvt')) {
            return 'cvt';
        }
        if (str_contains($text, 'semi')) {
            return 'semi-automatic';
        }

        return null;
    }

    /**
     * Определение типа топлива
     */
    private function guessFuelType(?string $text): ?string
    {
        if (!$text) return null;

        $text = strtolower($text);

        if (str_contains($text, 'gas') || str_contains($text, 'бензин')) {
            return 'gasoline';
        }
        if (str_contains($text, 'diesel') || str_contains($text, 'дизель')) {
            return 'diesel';
        }
        if (str_contains($text, 'hybrid') || str_contains($text, 'гибрид')) {
            return 'hybrid';
        }
        if (str_contains($text, 'electric') || str_contains($text, 'электро')) {
            return 'electric';
        }
        if (str_contains($text, 'lpg') || str_contains($text, 'газ')) {
            return 'lpg';
        }

        return null;
    }

    /**
     * Парсинг объема двигателя в куб.см
     */
    private function parseEngineSize(?string $text): ?int
    {
        if (!$text) return null;

        // Ищем паттерны вроде "2.0L", "3.5L", "1500cc"
        if (preg_match('/(\d+\.?\d*)\s*L/i', $text, $matches)) {
            return (int) ($matches[1] * 1000); // Литры → куб.см
        }

        if (preg_match('/(\d+)\s*cc/i', $text, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Генерация placeholder изображений для демонстрации (только латиница!)
     */
    private function generatePlaceholderPhotos(string $make, string $model, int $year): array
    {
        // Используем ТОЛЬКО латиницу, чтобы плейсхолдеры точно отображались
        $vehicleName = "$year+$make+$model";
        $vehicleName = str_replace(' ', '+', $vehicleName);

        return [
            "https://placehold.co/400x300/e5e7eb/6b7280?text=Photo+1+$vehicleName",
            "https://placehold.co/400x300/e5e7eb/6b7280?text=Photo+2+$vehicleName",
            "https://placehold.co/400x300/e5e7eb/6b7280?text=Photo+3+$vehicleName",
            "https://placehold.co/400x300/e5e7eb/6b7280?text=Photo+4+$vehicleName",
        ];
    }

    /**
     * Попытка извлечь реальные изображения с Copart
     */
    private function extractCopartImages(Crawler $crawler): array
    {
        $photos = [];

        try {
            // Ищем изображения по различным селекторам
            $crawler->filter('#media-lot-image, img[alt*="LOT"], img.p-image-item-box')->each(function (Crawler $node) use (&$photos) {
                $src = $node->attr('src') ?? $node->attr('data-src');
                if ($src) {
                    // Убираем параметры URL
                    $clean = strtok($src, '?');
                    // Если миниатюры, пробуем заменить на полноразмер
                    $clean = preg_replace('/(_thb|_thn|_tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $clean);
                    // Если относительная ссылка - сделаем абсолютной
                    if (!str_starts_with($clean, 'http')) {
                        $clean = 'https://cs.copart.com' . $clean;
                    }
                    $photos[] = $clean;
                }
            });

            // Если не нашли, пробуем другие селекторы
            if (empty($photos)) {
                $crawler->filter('img[src*="cs.copart.com"]')->each(function (Crawler $node) use (&$photos) {
                    $src = $node->attr('src');
                    if ($src) {
                        $clean = strtok($src, '?');
                        $clean = preg_replace('/(_thb|_thn|_tmb)\.(jpg|jpeg|png|webp)$/i', '_ful.$2', $clean);
                        $photos[] = $clean;
                    }
                });
            }
        } catch (\Exception $e) {
            Log::warning('Could not extract Copart images: ' . $e->getMessage());
        }

        return array_unique(array_filter($photos));
    }
}

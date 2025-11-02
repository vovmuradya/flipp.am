<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuctionParserService;

class AuctionListingController extends Controller
{
    /**
     * Получить данные автомобиля с аукциона по URL
     */
    public function fetchFromUrl(Request $request)
    {
        $validated = $request->validate(['url' => 'required|url']);
        $url = $validated['url'];

        try {
            /** @var AuctionParserService $service */
            $service = app(AuctionParserService::class);
            $parsed = $service->parseFromUrl($url); // array|null

            if (!$parsed) {
                $parsed = $this->fallbackFromUrl($url);
            }

            // Нормализация и валидация значений
            $vehicle = [
                'make' => $parsed['make'] ?? null,
                'model' => $parsed['model'] ?? null,
                'year' => isset($parsed['year']) && preg_match('/^(19|20)\d{2}$/', (string)$parsed['year']) ? (int)$parsed['year'] : null,
                'mileage' => isset($parsed['mileage']) && is_numeric($parsed['mileage']) ? (int)$parsed['mileage'] : null,
                'exterior_color' => $parsed['exterior_color'] ?? null,
                'transmission' => $parsed['transmission'] ?? 'automatic',
                'fuel_type' => $parsed['fuel_type'] ?? 'gasoline',
                'engine_displacement_cc' => isset($parsed['engine_displacement_cc']) && is_numeric($parsed['engine_displacement_cc']) ? (int)$parsed['engine_displacement_cc'] : null,
                'body_type' => $parsed['body_type'] ?? 'SUV',
                'photos' => array_values(array_filter($parsed['photos'] ?? [], fn($u) => is_string($u) && strlen($u) > 5)),
                'source_auction_url' => $parsed['source_auction_url'] ?? $url,
            ];

            // Заголовок и описание
            $titleParts = [];
            if ($vehicle['year']) { $titleParts[] = $vehicle['year']; }
            if ($vehicle['make']) { $titleParts[] = $vehicle['make']; }
            if ($vehicle['model']) { $titleParts[] = $vehicle['model']; }
            $title = trim(implode(' ', $titleParts));

            $desc = [
                'Автомобиль с аукциона',
                '',
                'Характеристики:',
                '• Марка: ' . ($vehicle['make'] ?? 'Не указано'),
                '• Модель: ' . ($vehicle['model'] ?? 'Не указано'),
                '• Год: ' . ($vehicle['year'] ?? 'Не указан'),
            ];
            if (!empty($vehicle['mileage'])) {
                $desc[] = '• Пробег: ' . number_format($vehicle['mileage'], 0, '.', ' ') . ' км';
            }
            if (!empty($vehicle['exterior_color'])) {
                $desc[] = '• Цвет: ' . $vehicle['exterior_color'];
            }
            if (!empty($vehicle['engine_displacement_cc'])) {
                $desc[] = '• Объем двигателя: ' . number_format((int)$vehicle['engine_displacement_cc'], 0, '.', ' ') . ' куб. см';
            }

            $response = [
                'title' => $title,
                'description' => implode("\n", $desc),
                'price' => null,
                'category_id' => 1,
                'auction_url' => $url,
                'vehicle' => $vehicle,
                'photos' => $vehicle['photos'],
            ];

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Данные успешно получены',
            ]);
        } catch (\Throwable $e) {
            Log::error('fetchFromUrl error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Фолбэк: извлечь год/марку/модель из слагов URL Copart/IAAI.
     */
    private function fallbackFromUrl(string $url): array
    {
        $out = [
            'make' => null,
            'model' => null,
            'year' => null,
            'photos' => [],
            'source_auction_url' => $url,
        ];

        $path = parse_url($url, PHP_URL_PATH);
        $slug = is_string($path) ? trim($path, '/') : '';
        $parts = $slug ? explode('-', $slug) : [];

        // Ищем валидный год
        $year = null; $yearPos = null;
        foreach ($parts as $i => $p) {
            if (preg_match('/^(19|20)\d{2}$/', $p)) { $year = (int)$p; $yearPos = $i; break; }
        }
        if ($year) { $out['year'] = $year; }

        // Список возможных марок (в нижнем регистре)
        $makes = [
            'acura','audi','bmw','buick','cadillac','chevrolet','chevy','chrysler','dodge','fiat','ford',
            'gmc','honda','hyundai','infiniti','jaguar','jeep','kia','land','rover','lexus','lincoln','mazda',
            'mercedes','benz','mini','mitsubishi','nissan','porsche','ram','subaru','tesla','toyota','volkswagen',
            'vw','volvo','saab','hummer','pontiac','saturn','scion','suzuki','alfa','romeo','peugeot','renault'
        ];

        if ($yearPos !== null) {
            $after = array_slice($parts, $yearPos + 1);
            // Отрезаем служебные куски (локации/статус)
            $stopWords = ['ak','al','ar','az','bc','ca','co','ct','dc','de','fl','ga','hi','ia','id','il','in','ks','ky','la','ma','mb','md','me','mi','mn','mo','ms','mt','nb','nc','nd','ne','nh','nj','nm','ns','nt','nu','ny','oh','ok','on','or','pa','pe','qc','ri','sc','sd','sk','tn','tx','ut','va','vt','wa','wi','wv','wy','yt','moncton','savannah'];
            $clean = [];
            foreach ($after as $token) {
                $lt = strtolower($token);
                if (in_array($lt, ['salvage','clean','title','rebuildable','certificate'])) { continue; }
                if (in_array($lt, $stopWords)) { break; }
                $clean[] = $token;
            }
            // Пытаемся найти make
            $make = null; $modelTokens = [];
            foreach ($clean as $idx => $t) {
                if (in_array(strtolower($t), $makes, true)) { $make = ucfirst(strtolower($t)); $modelTokens = array_slice($clean, $idx + 1); break; }
            }
            if (!$make && !empty($clean)) { // если не нашли в справочнике — берём первый как make
                $make = ucfirst(strtolower($clean[0]));
                $modelTokens = array_slice($clean, 1);
            }
            $out['make'] = $make;
            $out['model'] = $modelTokens ? ucfirst(implode(' ', array_map(fn($x) => strtolower($x), $modelTokens))) : null;
        }

        return $out;
    }
}

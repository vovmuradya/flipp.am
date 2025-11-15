<?php

namespace App\Jobs;

use App\Models\AuctionImport;
use App\Services\AuctionParserService;
use App\Support\VehicleAttributeOptions;
use App\Support\VehicleCategoryResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAuctionImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $importId)
    {
    }

    public function handle(AuctionParserService $parser): void
    {
        $import = AuctionImport::find($this->importId);

        if (!$import || $import->status !== AuctionImport::STATUS_PENDING) {
            return;
        }

        $import->update([
            'status' => AuctionImport::STATUS_PROCESSING,
            'error' => null,
        ]);

        try {
            $parsed = $parser->parseFromUrl($import->url, aggressive: (bool) config('services.copart.aggressive', false));

            if (!$parsed && $parser->wasCopartBlocked()) {
                $import->update([
                    'status' => AuctionImport::STATUS_FAILED,
                    'error' => 'Copart временно ограничил выдачу. Попробуйте снова через несколько секунд.',
                ]);
                return;
            }

            if (!$parsed) {
                $parsed = $this->fallbackAuctionData($import->url);
            }

            if (!$parsed || empty($parsed['make']) || empty($parsed['model'])) {
                $import->update([
                    'status' => AuctionImport::STATUS_FAILED,
                    'error' => 'Не удалось получить данные по указанной ссылке.',
                ]);
                return;
            }

            $payload = $this->buildPayload($parsed, $import->url);

            if (!$payload) {
                $import->update([
                    'status' => AuctionImport::STATUS_FAILED,
                    'error' => 'Категории для транспортных объявлений не настроены.',
                ]);
                return;
            }

            $import->update([
                'status' => AuctionImport::STATUS_SUCCESS,
                'payload' => $payload,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Auction import failed', [
                'import_id' => $import->id,
                'error' => $exception->getMessage(),
            ]);

            $import->update([
                'status' => AuctionImport::STATUS_FAILED,
                'error' => 'Не удалось загрузить данные с аукциона.',
            ]);
        }
    }

    private function buildPayload(array $parsed, string $url): ?array
    {
        $vehicle = [
            'make' => $parsed['make'] ?? null,
            'model' => $parsed['model'] ?? null,
            'year' => isset($parsed['year']) && preg_match('/^(19|20)\d{2}$/', (string) $parsed['year']) ? (int) $parsed['year'] : null,
            'mileage' => isset($parsed['mileage']) && is_numeric($parsed['mileage']) ? (int) $parsed['mileage'] : null,
            'exterior_color' => $parsed['exterior_color'] ?? null,
            'transmission' => $parsed['transmission'] ?? 'automatic',
            'fuel_type' => $parsed['fuel_type'] ?? 'gasoline',
            'engine_displacement_cc' => isset($parsed['engine_displacement_cc']) && is_numeric($parsed['engine_displacement_cc']) ? (int) $parsed['engine_displacement_cc'] : null,
            'body_type' => $parsed['body_type'] ?? null,
            'photos' => array_values(array_filter($parsed['photos'] ?? [], fn ($u) => is_string($u) && strlen($u) > 5)),
            'source_auction_url' => $parsed['source_auction_url'] ?? $url,
            'auction_ends_at' => $parsed['auction_ends_at'] ?? null,
            'buy_now_price' => isset($parsed['buy_now_price']) && $parsed['buy_now_price'] !== '' ? (float) $parsed['buy_now_price'] : null,
            'buy_now_currency' => $parsed['buy_now_currency'] ?? null,
            'operational_status' => $parsed['operational_status'] ?? null,
        ];

        $titleParts = [];
        if ($vehicle['year']) {
            $titleParts[] = $vehicle['year'];
        }
        if ($vehicle['make']) {
            $titleParts[] = $vehicle['make'];
        }
        if ($vehicle['model']) {
            $titleParts[] = $vehicle['model'];
        }

        $title = trim(implode(' ', $titleParts));

        $descriptionLines = [
            'Ավտոմեքենա աճուրդից',
            '',
            'Հատկություններ․',
            '• Մակնիշ․ ' . ($vehicle['make'] ?? 'չսահմանված'),
            '• Մոդել․ ' . ($vehicle['model'] ?? 'չսահմանված'),
            '• Տարեթիվ․ ' . ($vehicle['year'] ?? 'չսահմանված'),
        ];

        if (!empty($vehicle['mileage'])) {
            $descriptionLines[] = '• Վազք․ ' . number_format($vehicle['mileage'], 0, '.', ' ') . ' կմ';
        }

        if (!empty($vehicle['exterior_color'])) {
            $colorText = VehicleAttributeOptions::colorLabel($vehicle['exterior_color']) ?? $vehicle['exterior_color'];
            $descriptionLines[] = '• Գույն․ ' . $colorText;
        }

        if (!empty($vehicle['engine_displacement_cc'])) {
            $descriptionLines[] = '• Շարժիչ․ ' . number_format((int) $vehicle['engine_displacement_cc'], 0, '.', ' ') . ' խոր. սմ';
        }

        $categoryId = VehicleCategoryResolver::resolve();
        if (!$categoryId) {
            return null;
        }

        return [
            'title' => $title,
            'description' => implode("\n", $descriptionLines),
            'price' => $vehicle['buy_now_price'] ?? null,
            'category_id' => $categoryId,
            'auction_url' => $url,
            'vehicle' => $vehicle,
            'photos' => $vehicle['photos'],
        ];
    }

    private function fallbackAuctionData(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH);
        $slug = is_string($path) ? trim($path, '/') : '';

        $lastSegment = null;
        if ($slug !== '') {
            $segments = explode('/', $slug);
            $lastSegment = end($segments) ?: null;
        }

        $parts = $lastSegment ? array_filter(array_map('trim', explode('-', $lastSegment))) : [];

        $year = null;
        $yearPos = null;
        foreach ($parts as $index => $part) {
            if (preg_match('/^(19|20)\d{2}$/', $part)) {
                $year = (int) $part;
                $yearPos = $index;
                break;
            }
        }

        $makes = [
            'acura','audi','bmw','buick','cadillac','chevrolet','chevy','chrysler','dodge','fiat','ford','gmc','honda','hyundai','infiniti','jaguar','jeep','kia','land','rover','lexus','lincoln','mazda','mercedes','benz','mini','mitsubishi','nissan','porsche','ram','subaru','tesla','toyota','volkswagen','vw','volvo','saab','hummer','pontiac','saturn','scion','suzuki','alfa','romeo','peugeot','renault'
        ];

        $make = null;
        $modelTokens = [];

        if ($yearPos !== null) {
            $after = array_slice($parts, $yearPos + 1);
            $stopWords = ['salvage','clean','title','rebuildable','certificate'];
            $filtered = [];
            foreach ($after as $token) {
                $lower = strtolower($token);
                if (in_array($lower, $stopWords, true)) {
                    continue;
                }
                $filtered[] = $token;
            }

            foreach ($filtered as $idx => $token) {
                if (in_array(strtolower($token), $makes, true)) {
                    $make = ucfirst(strtolower($token));
                    $modelTokens = array_slice($filtered, $idx + 1);
                    break;
                }
            }

            if (!$make && !empty($filtered)) {
                $make = ucfirst(strtolower($filtered[0]));
                $modelTokens = array_slice($filtered, 1);
            }
        }

        $make = $make ? ucfirst(strtolower($make)) : null;
        $model = $modelTokens
            ? ucfirst(implode(' ', array_map(fn ($value) => strtolower($value), $modelTokens)))
            : null;

        if (!$make || !$model) {
            return null;
        }

        return [
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'mileage' => null,
            'exterior_color' => null,
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'engine_displacement_cc' => null,
            'body_type' => null,
            'photos' => [],
            'source_auction_url' => $url,
            'auction_ends_at' => null,
        ];
    }
}

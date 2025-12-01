<?php

namespace App\Services\Pricing;

use App\Models\Listing;

/**
 * Черновой сервис IMV/бейджей. Заменить на полноценную модель ценообразования.
 */
class ImvService
{
    public function __construct(
        private readonly ImvModel $model = new ImvModel(),
    ) {
    }

    /**
     * Возвращает расчет IMV и бейдж на основе грубой эвристики.
     * - imv_value: пока берем цену как базу, если указана.
     * - price_badge: great/fair/overpriced/unknown по отношению price/imv.
     * - price_anomaly: true, если цена не задана или выглядит некорректной.
     */
    public function analyze(Listing $listing): array
    {
        $price = $listing->price ? (float) $listing->price : null;

        // TODO: заменить на модель с данными рынка, сезонностью, регионом, пробегом.
        $features = [
            'price' => $price,
            'year' => optional($listing->vehicleDetail)->year,
            'mileage' => optional($listing->vehicleDetail)->mileage,
            'region_id' => $listing->region_id,
            'category_id' => $listing->category_id,
            'listing_type' => $listing->listing_type,
        ];

        $imv = $this->model->estimate($features);
        $badge = $this->badge($price, $imv);
        $anomaly = $this->isAnomalous($price, $imv);

        return [
            'imv_value' => $imv,
            'price_badge' => $badge,
            'price_anomaly' => $anomaly,
            'price_analysis' => [
                'source' => 'heuristic_placeholder',
                'inputs' => [
                    'price' => $price,
                    'year' => optional($listing->vehicleDetail)->year,
                    'mileage' => optional($listing->vehicleDetail)->mileage,
                    'region_id' => $listing->region_id,
                ],
                'notes' => 'Replace with real IMV model; see idromTZ.pdf Глава 1',
            ],
        ];
    }

    private function badge(?float $price, ?float $imv): ?string
    {
        if (!$price || !$imv || $imv <= 0) {
            return 'unknown';
        }

        $ratio = $price / $imv;

        if ($ratio <= 0.93) {
            return 'great';
        }

        if ($ratio <= 1.07) {
            return 'fair';
        }

        return 'overpriced';
    }

    private function isAnomalous(?float $price, ?float $imv): bool
    {
        if (!$price || $price <= 0) {
            return true;
        }

        if (!$imv || $imv <= 0) {
            return false;
        }

        return $price >= ($imv * 3) || $price <= ($imv * 0.2);
    }
}

<?php

namespace App\Services\Pricing;

use App\Models\Listing;

/**
 * Заготовка модели IMV: принимает фичи и возвращает оценку.
 * Сейчас — линейная эвристика; заменить на обучаемую модель или сервис.
 */
class ImvModel
{
    public function estimate(array $features): float
    {
        $base = $features['price'] ?? null;
        if (!$base || $base <= 0) {
            $base = 10000; // fallback
        }

        $year = $features['year'] ?? null;
        $mileage = $features['mileage'] ?? null;
        $regionFactor = $this->regionFactor($features['region_id'] ?? null);

        $agePenalty = $year ? max(0, (date('Y') - (int) $year)) * 150 : 0;
        $mileagePenalty = $mileage ? ($mileage / 1000) * 20 : 0;

        $imv = ($base - $agePenalty - $mileagePenalty) * $regionFactor;

        return max(100, round($imv, 2));
    }

    private function regionFactor(?int $regionId): float
    {
        if (!$regionId) {
            return 1.0;
        }

        // TODO: заменить на справочник/статистику (geo-арбитраж)
        return match ($regionId) {
            default => 1.0,
        };
    }
}

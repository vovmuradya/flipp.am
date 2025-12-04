<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class TariffService
{
    /**
     * Возвращает цену доставки для указанного штата/локации (state) из storage/app/tariffs.json.
     */
    public static function getPrice(string $state): ?int
    {
        $state = trim($state);
        if ($state === '') {
            return null;
        }

        static $cache = null;
        if ($cache === null) {
            if (!Storage::exists('tariffs.json')) {
                $cache = [];
            } else {
                $json = Storage::get('tariffs.json');
                $cache = json_decode($json, true) ?: [];
            }
        }

        foreach ($cache as $row) {
            if (!isset($row['state'], $row['price'])) {
                continue;
            }
            if (strcasecmp($row['state'], $state) === 0) {
                return is_numeric($row['price']) ? (int) $row['price'] : null;
            }
        }

        return null;
    }
}

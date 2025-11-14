<?php

namespace App\Console\Commands;

use App\Services\CopartCookieManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UpdateCopartCookiesCommand extends Command
{
    protected $signature = 'copart:update-cookies {cookies? : Строка cookies, скопированная из DevTools}';

    protected $description = 'Обновить значение COPART_COOKIES в .env';

    public function handle(): int
    {
        $raw = $this->argument('cookies');

        if (!$raw) {
            $this->info('Вставьте cookies целиком (например, значение заголовка Cookie из DevTools).');
            $raw = $this->ask('Cookies');
        }

        if (!is_string($raw) || trim($raw) === '') {
            $this->error('Пустое значение. Обновление отменено.');
            return self::FAILURE;
        }

        $cookies = trim($raw);
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env не найден. Сначала создайте .env файл.');
            return self::FAILURE;
        }

        if (!$this->writeEnvValue($envPath, 'COPART_COOKIES', $cookies)) {
            $this->error('Не удалось записать значение в .env.');
            return self::FAILURE;
        }

        // Обновляем конфиг и кеш, чтобы новое значение использовалось сразу.
        config(['services.copart.cookies' => $cookies]);
        Cache::put(CopartCookieManager::CACHE_KEY, $cookies, now()->addHours(6));

        $this->info('COPART_COOKIES успешно обновлена.');
        $this->line('Не забудьте перезапустить очередь / Horizon, если они используются.');

        return self::SUCCESS;
    }

    private function writeEnvValue(string $envPath, string $key, string $value): bool
    {
        $contents = file_get_contents($envPath);
        if ($contents === false) {
            return false;
        }

        $formatted = $this->formatEnvValue($value);
        $line = $key . '=' . $formatted;

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $line, $contents);
        } else {
            $contents = rtrim($contents) . PHP_EOL . $line . PHP_EOL;
        }

        return file_put_contents($envPath, $contents) !== false;
    }

    private function formatEnvValue(string $value): string
    {
        $needsQuotes = Str::contains($value, ['"', "'", ' ', '#', '=']);

        if (!$needsQuotes) {
            return $value;
        }

        $escaped = str_replace('"', '\\"', $value);

        return '"' . $escaped . '"';
    }
}

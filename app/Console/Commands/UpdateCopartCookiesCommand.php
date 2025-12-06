<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class UpdateCopartCookiesCommand extends Command
{
    protected $signature = 'copart:update-cookies';

    protected $description = 'Обновить COPART_COOKIES через Puppeteer и перезагрузить конфиг';

    public function handle(): int
    {
        $script = base_path('scraper/fetch-copart-cookies.cjs');
        if (! File::exists($script)) {
            $this->error("Скрипт не найден: {$script}");
            return self::FAILURE;
        }

        $process = new Process(['node', $script], base_path(), null, null, 80);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
            Log::warning('copart:update-cookies failed', ['error' => $error]);
            $this->error("Не удалось обновить cookies: {$error}");
            return self::FAILURE;
        }

        $output = trim($process->getOutput());
        $decoded = json_decode($output, true);
        $cookies = is_array($decoded) ? ($decoded['cookies'] ?? null) : null;
        $count = is_array($decoded) ? (int) ($decoded['count'] ?? 0) : 0;

        if (! is_string($cookies) || trim($cookies) === '') {
            $this->error('Скрипт вернул пустые cookies');
            return self::FAILURE;
        }

        $this->writeEnvCookie($cookies);

        $this->callSilent('config:clear');
        $this->info("Cookies обновлены ✔ ({$count})");

        return self::SUCCESS;
    }

    private function writeEnvCookie(string $cookies): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }

        $env = File::get($envPath);
        $line = 'COPART_COOKIES="' . $cookies . '"';

        if (preg_match('/^COPART_COOKIES=.*$/m', $env)) {
            $env = preg_replace('/^COPART_COOKIES=.*$/m', $line, $env);
        } else {
            $env .= PHP_EOL . $line . PHP_EOL;
        }

        File::put($envPath, $env);
    }
}

<?php

namespace App\Console\Commands;

use App\Services\CopartCookieManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RefreshCopartCookies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copart:refresh-cookies {--silent : Do not output cookies to the console}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch fresh Copart cookies via the scraper and store them in the environment file.';

    public function handle(): int
    {
        $script = base_path('scraper/fetch-copart-cookies.cjs');

        if (! is_file($script)) {
            $this->error('Copart cookie fetcher script not found: '.$script);

            return self::FAILURE;
        }

        $process = new Process(
            ['node', $script],
            base_path(),
            [
                'COPART_USER_AGENT' => config('services.copart.user_agent'),
            ],
            null,
            60
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $this->error('Failed to fetch cookies: '.$exception->getMessage());

            return self::FAILURE;
        }

        $output = trim($process->getOutput());
        $payload = json_decode($output, true);

        if (! is_array($payload) || empty($payload['cookies'])) {
            $this->error('Fetcher script did not return cookies. Raw output: '.$output);

            return self::FAILURE;
        }

        $cookies = $payload['cookies'];
        $pairs = (int) ($payload['count'] ?? 0);

        if (! $this->writeEnvValue('COPART_COOKIES', $cookies)) {
            $this->error('Unable to persist COPART_COOKIES to the .env file.');

            return self::FAILURE;
        }

        config(['services.copart.cookies' => $cookies]);
        Cache::put(CopartCookieManager::CACHE_KEY, $cookies, now()->addHours(6));

        if (! $this->option('silent')) {
            $this->info(sprintf('Updated COPART_COOKIES with %d cookie pairs.', $pairs));
        }

        return self::SUCCESS;
    }

    protected function writeEnvValue(string $key, string $value): bool
    {
        $path = base_path('.env');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error('.env file is missing or unreadable.');

            return false;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return false;
        }

        $sanitizedValue = Str::of($value)
            ->replace(["\r\n", "\r", "\n"], '')
            ->trim()
            ->toString();

        // Wrap in quotes to avoid breaking on special characters.
        $quoted = '"'.addslashes($sanitizedValue).'"';
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $key.'='.$quoted, $contents);
        } else {
            $contents .= PHP_EOL.$key.'='.$quoted.PHP_EOL;
        }

        return file_put_contents($path, $contents) !== false;
    }
}

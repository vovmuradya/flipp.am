<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CopartService
{
    private const LOT_API = 'https://www.copart.com/public/data/lotdetails/solr/';

    /**
     * @return array{data:mixed,fallback:bool}
     */
    public function getLot(string $id): array
    {
        $direct = $this->fetchDirect($id);
        if ($direct !== null) {
            return ['data' => $direct, 'fallback' => false];
        }

        $fallback = $this->fetchFallback($id);

        return ['data' => $fallback, 'fallback' => true];
    }

    private function fetchDirect(string $id): ?array
    {
        $cookies = trim((string) config('services.copart.cookies', env('COPART_COOKIES', '')));

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'Accept' => 'application/json, text/plain, */*',
                    'User-Agent' => config('services.copart.user_agent'),
                    'Cookie' => $cookies ?: null,
                ])
                ->get(self::LOT_API . $id);

            $body = $response->body();
            $payload = json_decode($body, true);

            if ($response->successful() && is_array($payload)) {
                return $payload;
            }
        } catch (\Throwable $e) {
            Log::warning('Copart direct fetch failed', ['lot' => $id, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function fetchFallback(string $id): mixed
    {
        $script = base_path('scraper/fetch-copart-lot.cjs');
        if (! File::exists($script)) {
            Log::error('Copart fallback script missing', ['path' => $script]);
            return null;
        }

        $process = new Process(['node', $script, $id], base_path('scraper'), null, null, 45);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
            Log::warning('Copart fallback failed', ['lot' => $id, 'error' => $error]);
            return null;
        }

        $output = trim($process->getOutput());
        $decoded = json_decode($output, true);

        return $decoded ?? $output;
    }
}

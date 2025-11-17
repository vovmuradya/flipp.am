<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MsgrushSmsClient
{
    private ?string $baseUrl;
    private ?string $apiKey;
    private string $defaultSender;

    public function __construct()
    {
        $config = config('services.msgrush', []);
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = $config['api_key'] ?? null;
        $this->defaultSender = $config['default_sender'] ?? 'idrom.am';
    }

    public function isEnabled(): bool
    {
        return filled($this->baseUrl) && filled($this->apiKey);
    }

    /**
     * @throws RequestException
     */
    public function send(string $phone, string $message, ?string $sender = null): void
    {
        if (! $this->isEnabled()) {
            Log::warning('Msgrush client skipped: configuration missing.');
            return;
        }

        $payload = [
            'sender_id' => $sender ?: $this->defaultSender,
            'recipients' => [$phone],
            'message' => $message,
        ];

        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(15)
            ->post($this->baseUrl . '/sms-api/send', $payload);

        if ($response->failed()) {
            Log::error('Msgrush SMS send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();
        }

        $data = $response->json();
        Log::info('Msgrush SMS sent', [
            'recipients' => $data['sent'] ?? 0,
            'cost' => $data['total_cost'] ?? null,
            'response' => $data ?? $response->body(),
        ]);
    }
}

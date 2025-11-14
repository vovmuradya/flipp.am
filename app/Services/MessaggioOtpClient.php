<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MessaggioOtpClient
{
    private ?string $apiKey;
    private ?string $sender;
    private string $channel;
    private int $ttl;
    private string $template;
    private string $endpoint;
    private int $timeout;

    public function __construct()
    {
        $config = config('services.messaggio', []);
        $this->apiKey = $config['api_key'] ?? null;
        $this->sender = $config['sender'] ?? null;
        $this->channel = $config['channel'] ?? 'sms';
        $this->ttl = (int) ($config['ttl'] ?? 600);
        $this->template = $config['template'] ?? 'Ваш код подтверждения: :code';
        $this->endpoint = $config['send_url'] ?? '';
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey) && !empty($this->endpoint);
    }

    /**
     * @throws RequestException
     */
    public function send(string $phone, string $code): void
    {
        if (!$this->isEnabled()) {
            Log::warning('Messaggio OTP skipped: client is not configured.');
            return;
        }

        $payload = array_filter([
            'destination' => $phone,
            'channel' => $this->channel,
            'ttl' => $this->ttl,
            'sender' => $this->sender,
            'content' => Str::of($this->template)->replace(':code', $code)->value(),
        ], static fn ($value) => $value !== null && $value !== '');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->retry(2, 500)
            ->post($this->endpoint, $payload);

        if ($response->failed()) {
            Log::error('Messaggio OTP send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();
        }
    }
}

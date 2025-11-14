<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PushNotificationService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $settings = $user->notification_settings ?? [];
        if (($settings['messages'] ?? true) === false) {
            return;
        }

        $tokens = $user->devices()
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->all();

        if (empty($tokens)) {
            return;
        }

        foreach (array_chunk($tokens, 500) as $chunk) {
            $this->sendFcmChunk($chunk, $title, $body, $data);
        }
    }

    private function sendFcmChunk(array $tokens, string $title, string $body, array $data = []): void
    {
        $serverKey = config('push.fcm.server_key');
        $endpoint = config('push.fcm.endpoint', 'https://fcm.googleapis.com/fcm/send');

        if (!$serverKey) {
            Log::warning('FCM server key is not configured, cannot deliver push notification.');
            return;
        }

        try {
            Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ])->throw();
        } catch (\Throwable $e) {
            Log::error('FCM push failed', [
                'error' => $e->getMessage(),
                'tokens_count' => count($tokens),
            ]);
        }
    }
}

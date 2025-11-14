<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\SendMessageRequest;
use App\Http\Resources\ChatSummaryResource;
use App\Http\Resources\MessageResource;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min($request->integer('per_page', 20) ?? 20, 50));
        $page = max(1, $request->integer('page', 1) ?? 1);

        $messages = Message::query()
            ->with([
                'listing.media',
                'listing.vehicleDetail',
                'sender',
                'receiver',
            ])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get();

        $unreadMap = Message::query()
            ->selectRaw('listing_id, sender_id, COUNT(*) as unread_count')
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->groupBy('listing_id', 'sender_id')
            ->get()
            ->mapWithKeys(fn ($row) => [sprintf('%s:%s', $row->listing_id, $row->sender_id) => $row->unread_count]);

        $conversations = $this->buildConversationCollection($messages, $user->id, $unreadMap);
        $total = $conversations->count();
        $items = $conversations->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return ChatSummaryResource::collection($items)->additional([
            'status' => 'success',
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function messages(Request $request, Listing $listing)
    {
        [$user, $participant] = $this->resolveParticipant($request, $listing);
        $perPage = max(1, min($request->integer('per_page', 30) ?? 30, 100));

        $messages = Message::query()
            ->with(['sender', 'receiver'])
            ->where('listing_id', $listing->id)
            ->where(function ($q) use ($user, $participant) {
                $q->where(function ($inner) use ($user, $participant) {
                    $inner->where('sender_id', $user->id)
                        ->where('receiver_id', $participant->id);
                })->orWhere(function ($inner) use ($user, $participant) {
                    $inner->where('sender_id', $participant->id)
                        ->where('receiver_id', $user->id);
                });
            })
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $this->markConversationRead($listing->id, $participant->id, $user->id);

        return MessageResource::collection($messages)->additional([
            'status' => 'success',
        ]);
    }

    public function send(SendMessageRequest $request, Listing $listing): JsonResponse
    {
        [$user, $receiver] = $this->determineReceiver($request, $listing);

        $message = Message::create([
            'listing_id' => $listing->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'body' => $request->input('body'),
            'is_read' => false,
        ]);

        $message->load(['sender', 'receiver']);

        app(PushNotificationService::class)->sendToUser(
            $receiver,
            __('Новое сообщение'),
            Str::limit($message->body, 140),
            [
                'type' => 'chat',
                'listing_id' => $listing->id,
                'message_id' => $message->id,
                'sender_id' => $user->id,
            ]
        );

        return $this->success(new MessageResource($message), __('Сообщение отправлено.'));
    }

    public function markAsRead(Request $request, Listing $listing): JsonResponse
    {
        [, $participant] = $this->resolveParticipant($request, $listing);
        $user = $request->user();

        $updated = $this->markConversationRead($listing->id, $participant->id, $user->id);

        return $this->success([
            'updated' => $updated,
        ], __('Диалог прочитан.'));
    }

    private function buildConversationCollection(Collection $messages, int $userId, Collection $unreadMap): Collection
    {
        $conversations = [];

        foreach ($messages as $message) {
            $counterparty = $message->sender_id === $userId ? $message->receiver : $message->sender;
            if (! $counterparty) {
                continue;
            }

            $key = sprintf('%s:%s', $message->listing_id, $counterparty->id);

            if (!isset($conversations[$key])) {
                $unreadKey = sprintf('%s:%s', $message->listing_id, $counterparty->id);
                $conversations[$key] = (object) [
                    'listing' => $message->listing,
                    'counterparty' => $counterparty,
                    'last_message' => $message,
                    'unread_count' => $unreadMap->get($unreadKey, 0),
                ];
            }
        }

        return collect($conversations);
    }

    private function resolveParticipant(Request $request, Listing $listing): array
    {
        $user = $request->user();
        $participantId = $request->integer('participant_id');

        if ($user->id === $listing->user_id) {
            if (!$participantId) {
                throw ValidationException::withMessages([
                    'participant_id' => __('Укажите участника диалога.'),
                ]);
            }
        } else {
            $participantId = $listing->user_id;
        }

        $participant = User::query()->findOrFail($participantId);

        return [$user, $participant];
    }

    private function determineReceiver(Request $request, Listing $listing): array
    {
        $user = $request->user();

        if ($user->id === $listing->user_id) {
            $receiverId = $request->integer('receiver_id');
            if (!$receiverId) {
                throw ValidationException::withMessages([
                    'receiver_id' => __('Укажите получателя.'),
                ]);
            }
            $receiver = User::query()->findOrFail($receiverId);
        } else {
            if ($listing->user_id === $user->id) {
                throw ValidationException::withMessages([
                    'receiver_id' => __('Нельзя отправлять сообщение самому себе.'),
                ]);
            }
            $receiver = $listing->user;
        }

        if ($receiver->id === $user->id) {
            throw ValidationException::withMessages([
                'receiver_id' => __('Нельзя отправить сообщение самому себе.'),
            ]);
        }

        return [$user, $receiver];
    }

    private function markConversationRead(int $listingId, int $participantId, int $userId): int
    {
        return Message::query()
            ->where('listing_id', $listingId)
            ->where('sender_id', $participantId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}

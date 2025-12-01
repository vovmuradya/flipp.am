<?php

namespace App\Http\Controllers\Api\Calls;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Calls\CallMaskingService;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallMaskingController extends Controller
{
    public function __construct(
        private readonly CallMaskingService $service
    ) {
    }

    /**
     * Создать прокси-сессию звонка (call masking).
     */
    public function start(Request $request)
    {
        $request->validate([
            'listing_id' => ['required', 'exists:listings,id'],
        ]);

        $listing = Listing::with('user')->findOrFail($request->listing_id);

        $session = $this->service->start([
            'listing_id' => $listing->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $listing->user_id,
            'seller_number' => $listing->user?->phone,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'proxy_number' => $session->proxy_number,
                'session_id' => $session->id,
                'status' => $session->status,
            ],
        ]);
    }

    /**
     * Завершить сессию звонка (фиксируем длительность/статус).
     */
    public function end(Request $request)
    {
        $request->validate([
            'session_id' => ['required', 'integer', 'exists:call_sessions,id'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $session = CallSession::findOrFail($request->session_id);

        // Опциональная авторизация: владелец (seller) или инициатор (buyer)
        if (!in_array(Auth::id(), [$session->buyer_id, $session->seller_id])) {
            abort(403);
        }

        $this->service->end($session, $request->input('duration_seconds'));

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $session->status,
                'duration_seconds' => $session->duration_seconds,
                'ended_at' => optional($session->ended_at)->toIso8601String(),
            ],
        ]);
    }
}

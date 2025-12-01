<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\EscrowTransaction;
use App\Services\Payments\EscrowService;
use App\Http\Resources\EscrowTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscrowController extends Controller
{
    public function __construct(
        private readonly EscrowService $escrow
    ) {
    }

    /**
     * Создать hold (эскроу) для сделки.
     * TODO: интегрировать с конкретным провайдером и статусами.
     */
    public function create(Request $request)
    {
        $request->validate([
            'listing_id' => ['required', 'exists:listings,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $listing = Listing::findOrFail($request->listing_id);

        $payload = [
            'buyer_id' => Auth::id(),
            'seller_id' => $listing->user_id,
            'amount' => (float) $request->amount,
            'currency' => strtoupper($request->currency),
            'listing_id' => $listing->id,
        ];

        $result = $this->escrow->createHold($payload);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Вебхук обновления статуса (заглушка).
     */
    public function webhook(Request $request)
    {
        $request->validate([
            'hold_id' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $tx = EscrowTransaction::where('hold_id', $request->hold_id)->first();
        if (!$tx) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $tx->status = $request->status;
        $tx->save();

        return response()->json(['success' => true]);
    }

    /**
     * Список эскроу-транзакций для текущего пользователя (buyer или seller).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $items = EscrowTransaction::query()
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return EscrowTransactionResource::collection($items)->additional([
            'status' => 'success',
        ]);
    }

    /**
     * Захват средств (stub). В проде должен вызываться webhook провайдера.
     */
    public function capture(Request $request)
    {
        $request->validate([
            'hold_id' => ['required', 'string', 'exists:escrow_transactions,hold_id'],
        ]);

        $this->escrow->capture($request->hold_id);

        return response()->json(['success' => true]);
    }

    /**
     * Разблокировка средств (stub).
     */
    public function release(Request $request)
    {
        $request->validate([
            'hold_id' => ['required', 'string', 'exists:escrow_transactions,hold_id'],
        ]);

        $this->escrow->release($request->hold_id);

        return response()->json(['success' => true]);
    }
}

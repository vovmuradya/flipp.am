<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingRefreshController extends Controller
{
    public function refresh(Request $request, int $id): JsonResponse
    {
        $listing = Listing::query()->findOrFail($id);

        if ($request->user()->id !== $listing->user_id) {
            abort(403);
        }

        if (!$listing->canBeRefreshed()) {
            return response()->json([
                'status' => 'error',
                'message' => __('listing.wait', ['hours' => $listing->hoursLeft()]),
            ], 422);
        }

        $listing->refreshed_at = now();
        $listing->save();

        return response()->json([
            'status' => 'ok',
            'refreshed_at' => $listing->refreshed_at->toIso8601String(),
        ]);
    }
}

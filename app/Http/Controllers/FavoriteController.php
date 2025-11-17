<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Listing $listing)
    {
        $user = $request->user();
        $user->favorites()->toggle($listing->id);
        $favorited = $user->favorites()->where('listing_id', $listing->id)->exists();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
            ]);
        }

        return back();
    }
}

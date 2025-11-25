<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use Illuminate\Http\Request;

class DealerProfileController extends Controller
{
    public function index()
    {
        $dealers = DealerProfile::query()
            ->withCount('listings')
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('dealers.index', compact('dealers'));
    }

    public function show(string $slug)
    {
        $dealer = DealerProfile::query()
            ->with(['user', 'listings' => function ($q) {
                $q->orderByRefreshed();
            }])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('dealers.show', compact('dealer'));
    }
}

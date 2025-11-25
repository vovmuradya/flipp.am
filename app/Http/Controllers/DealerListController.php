<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use Illuminate\Http\Request;

class DealerListController extends Controller
{
    public function index()
    {
        $dealers = DealerProfile::query()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('is_dealer', true))
            ->latest()
            ->take(20)
            ->get();

        return view('dealers.carousel', compact('dealers'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DealerProfile;
use Illuminate\Support\Facades\Auth;

class DealerController extends Controller
{
    public function edit()
    {
        $profile = DealerProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            ['company_name' => '', 'slug' => '']
        );

        return view('dealers.settings', compact('profile'));
    }
}

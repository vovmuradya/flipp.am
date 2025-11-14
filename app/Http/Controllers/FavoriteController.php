<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Listing $listing)
    {
        // Находим связь "favorites" у текущего пользователя и "переключаем"
        // ID объявления. Laravel сам добавит или удалит запись в таблице.
        auth()->user()->favorites()->toggle($listing->id);

        return back();
    }
}

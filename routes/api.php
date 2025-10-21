<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryFieldController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Стандартный маршрут Laravel для получения данных пользователя через API
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Наш маршрут для динамической подгрузки полей, который мы перенесли из web.php
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);

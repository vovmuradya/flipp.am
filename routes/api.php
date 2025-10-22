<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CarModelController;
use App\Http\Controllers\Api\CategoryFieldController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Категории
Route::get('/categories/root', [CategoryController::class, 'getRoot']);
Route::get('/categories/{category}/children', [CategoryController::class, 'getChildren']);

// Модели по бренду
Route::get('/brands/{brand}/models', [CarModelController::class, 'getByBrand']);

// Вот ЕДИНСТВЕННЫЙ правильный маршрут для полей
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);

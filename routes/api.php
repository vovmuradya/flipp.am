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

// Модели по бренду (оставляем getByBrand, т.к. контроллер его реализует)
Route::get('/brands/{brand}/models', [CarModelController::class, 'getByBrand']);

// Маршрут для полей
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);
Route::get('/models/{model}/generations', [CarModelController::class, 'getGenerationsByModel']);
Route::get('/models/{modelId}/generations', [\App\Http\Controllers\Api\GenerationController::class, 'getGenerationsByModel']);

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CarModelController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Категории
Route::get('/categories/root', [CategoryController::class, 'getRoot']);
Route::get('/categories/{category}/children', [CategoryController::class, 'getChildren']);
Route::get('/categories/{category}/fields', [CategoryController::class, 'fields']);

// Модели по бренду
Route::get('/brands/{brand}/models', [CarModelController::class, 'getByBrand']);

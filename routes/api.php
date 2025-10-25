<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CarModelController;
use App\Http\Controllers\Api\CategoryFieldController;
use App\Http\Controllers\Api\GenerationController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== CATEGORIES ====================
Route::get('/categories/root', [CategoryController::class, 'getRoot']);
Route::get('/categories/{category}/children', [CategoryController::class, 'getChildren']);
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);

// ==================== BRANDS & MODELS ====================
Route::get('/brands/{brand}/models', [CarModelController::class, 'getByBrand']);

// ==================== GENERATIONS ====================
// ✅ FIXED: Single route pointing to GenerationController
Route::get('/models/{modelId}/generations', [GenerationController::class, 'getGenerationsByModel']);

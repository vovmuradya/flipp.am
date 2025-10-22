<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публичные
Route::get('/', [ListingController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::resource('listings', ListingController::class)->only(['index']);

// Защищённые
Route::middleware('auth')->group(function () {
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::resource('listings', ListingController::class)->except(['index','show','create','store']);

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'myListings'])->name('index');
        Route::get('/my-listings', [DashboardController::class, 'myListings'])->name('my-listings');
        Route::get('/favorites', [DashboardController::class, 'favorites'])->name('favorites');
        Route::get('/messages', [DashboardController::class, 'messages'])->name('messages');
        Route::get('/messages/{listing}/{participant}', [DashboardController::class, 'showConversation'])->name('conversation.show');
        Route::post('/messages/{listing}/{participant}', [DashboardController::class, 'replyToConversation'])->name('conversation.reply');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::post('/listings/{listing}/messages', [MessageController::class, 'store'])->name('listings.messages.store');
    Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite.toggle');
    Route::post('/listings/{listing}/reviews', [ReviewController::class, 'store'])->name('listings.reviews.store');
});

// Публичные resource-маршруты
Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');

require __DIR__.'/auth.php';
//asdsadasdasddfdsf

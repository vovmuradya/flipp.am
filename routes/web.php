<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Api\AuctionListingController; // ТЗ v2.1
use App\Http\Controllers\ImageProxyController; // Прокси изображений
use App\Http\Controllers\ProxyController; // Новый прокси-контроллер

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публичные
Route::get('/', [ListingController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::resource('listings', ListingController::class)->only(['index']);

// Прокси изображений (публично, т.к. фото в публичных объявлениях)
Route::get('/image-proxy', [ImageProxyController::class, 'show'])->name('image.proxy');
Route::get('/proxy/image', [ProxyController::class, 'image'])->name('proxy.image'); // Новый маршрут для прокси-контроллера изображений

// Защищённые
Route::middleware('auth')->group(function () {
    Route::get('/listings/create/choose', [ListingController::class, 'createChoice'])->name('listings.create-choice');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::get('/listings/create-from-auction', [ListingController::class, 'createFromAuction'])->name('listings.create-from-auction');

    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::resource('listings', ListingController::class)->except(['index','show','create','store']);

    // ✅ ТЗ v2.1: РАЗДЕЛЕНИЕ ОБЪЯВЛЕНИЙ: Маршруты для аукционных объявлений
    Route::resource('auction-listings', \App\Http\Controllers\AuctionListingController::class)
        ->middleware(['auth']);

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'myListings'])->name('index');
        Route::get('/my-listings', [DashboardController::class, 'myListings'])->name('my-listings');

        // ✅ НОВЫЙ МАРШРУТ: Мои аукционные объявления
        Route::get('/my-auctions', [DashboardController::class, 'myAuctions'])->name('my-auctions');

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
    Route::post('/listings/draft', [ListingController::class, 'storeDraft'])->name('listings.draft');

    // ✅ Сохранение данных аукциона в сессию перед переходом к форме
    Route::post('/listings/save-auction-data', [ListingController::class, 'saveAuctionData'])->name('listings.save-auction-data');

    // ТЗ v2.1: API для парсинга аукционов (работает с веб-сессией)
    Route::post('/api/v1/dealer/listings/fetch-from-url', [\App\Http\Controllers\Api\AuctionListingController::class, 'fetchFromUrl'])->name('api.auction.fetch');
});

// Публичные resource-маршруты
Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');

require __DIR__.'/auth.php';

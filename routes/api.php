<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CarBrandController;
use App\Http\Controllers\Api\CarModelController;
use App\Http\Controllers\Api\CategoryFieldController;
use App\Http\Controllers\Api\GenerationController;
use App\Http\Controllers\Api\AuctionListingController; // ТЗ v2.1
use App\Http\Controllers\Api\AuctionParserController; // ТЗ v2.1 - Парсер аукционов
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Mobile\ChatController as MobileChatController;
use App\Http\Controllers\Api\Mobile\DeviceController as MobileDeviceController;
use App\Http\Controllers\Api\Mobile\FavoriteController as MobileFavoriteController;
use App\Http\Controllers\Api\Mobile\ListingController as MobileListingController;
use App\Http\Controllers\Api\Mobile\MyListingController as MobileMyListingController;
use App\Http\Controllers\Api\Mobile\ProfileController as MobileProfileController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== CATEGORIES ====================
Route::get('/categories/root', [CategoryController::class, 'getRoot']);
Route::get('/categories/{category}/children', [CategoryController::class, 'getChildren']);
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);

// ==================== BRANDS & MODELS ====================
Route::get('/brands', [CarBrandController::class, 'index']);
Route::get('/brands/{brand}/models', [CarBrandController::class, 'getModels']);
Route::get('/models/{modelId}/generations', [GenerationController::class, 'getGenerationsByModel']);

// ==================== AUCTION PARSER (ТЗ v2.1) ====================
Route::post('/v1/dealer/listings/fetch-from-url', [AuctionParserController::class, 'fetchFromUrl'])
    ->middleware('auth:sanctum')
    ->name('api.auction.fetch');

// ==================== MOBILE API ====================
Route::prefix('mobile')
    ->as('api.mobile.')
    ->group(function () {
        Route::get('/listings', [MobileListingController::class, 'index'])->name('listings.index');
        Route::get('/listings/{listing}', [MobileListingController::class, 'show'])->name('listings.show');

        Route::prefix('auth')->group(function () {
            Route::post('/phone/send-code', [MobileAuthController::class, 'sendVerificationCode'])
                ->name('auth.phone.send-code');
            Route::post('/email/send-code', [MobileAuthController::class, 'sendEmailVerificationCode'])
                ->name('auth.email.send-code');
            Route::post('/register', [MobileAuthController::class, 'register'])->name('auth.register');
            Route::post('/login', [MobileAuthController::class, 'login'])->name('auth.login');
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [MobileAuthController::class, 'me'])->name('auth.me');
            Route::post('/auth/logout', [MobileAuthController::class, 'logout'])->name('auth.logout');
            Route::post('/auth/refresh', [MobileAuthController::class, 'refreshToken'])->name('auth.refresh');

            Route::get('/profile', [MobileProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile', [MobileProfileController::class, 'update'])->name('profile.update');
            Route::get('/notification-settings', [MobileProfileController::class, 'notificationSettings'])->name('notifications.index');
            Route::put('/notification-settings', [MobileProfileController::class, 'updateNotificationSettings'])->name('notifications.update');

            Route::get('/devices', [MobileDeviceController::class, 'index'])->name('devices.index');
            Route::post('/devices', [MobileDeviceController::class, 'store'])->name('devices.store');
            Route::delete('/devices/{device}', [MobileDeviceController::class, 'destroy'])->name('devices.destroy');

            Route::get('/my/listings', [MobileMyListingController::class, 'index'])->name('my.listings.index');
            Route::get('/my/auctions', [MobileMyListingController::class, 'auctions'])->name('my.auctions.index');
            Route::post('/listings', [MobileMyListingController::class, 'store'])->name('my.listings.store');
            Route::put('/listings/{listing}', [MobileMyListingController::class, 'update'])->name('my.listings.update');
            Route::delete('/listings/{listing}', [MobileMyListingController::class, 'destroy'])->name('my.listings.destroy');
            Route::post('/listings/{listing}/bump', [MobileMyListingController::class, 'bump'])->name('my.listings.bump');

            Route::get('/chats', [MobileChatController::class, 'index'])->name('chats.index');
            Route::get('/listings/{listing}/messages', [MobileChatController::class, 'messages'])->name('chats.messages');
            Route::post('/listings/{listing}/messages', [MobileChatController::class, 'send'])->name('chats.send');
            Route::post('/listings/{listing}/messages/read', [MobileChatController::class, 'markAsRead'])->name('chats.read');

            Route::get('/favorites', [MobileFavoriteController::class, 'index'])->name('favorites.index');
            Route::post('/listings/{listing}/favorite', [MobileFavoriteController::class, 'store'])->name('favorites.store');
            Route::delete('/listings/{listing}/favorite', [MobileFavoriteController::class, 'destroy'])->name('favorites.destroy');
        });
    });

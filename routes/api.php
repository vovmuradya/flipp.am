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
use App\Services\CopartService;
use App\Http\Controllers\Api\Payments\EscrowController;
use App\Http\Controllers\Api\Calls\CallMaskingController;
use App\Http\Controllers\ImportCalculatorController;
use App\Http\Controllers\AuctionCarsCalculatorController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== CATEGORIES ====================
Route::get('/categories/root', [CategoryController::class, 'getRoot']);
Route::get('/categories/{category}/children', [CategoryController::class, 'getChildren']);
Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);

// ==================== IMPORT CALCULATOR (SRC.AM) ====================
Route::post('/import-calculator', [ImportCalculatorController::class, 'calculateImportCost'])
    ->name('api.import-calculator');

// ==================== COPART CALCULATOR (NO CSRF) ====================
Route::post('/copart-calculator/calculate', [AuctionCarsCalculatorController::class, 'calculate'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('api.copart-calculator.calculate');

// ==================== BRANDS & MODELS ====================
Route::get('/brands', [CarBrandController::class, 'index']);
Route::get('/brands/{brand}/models', [CarBrandController::class, 'getModels']);
Route::get('/models/{modelId}/generations', [GenerationController::class, 'getGenerationsByModel']);

// ==================== AUCTION PARSER (ТЗ v2.1) ====================
Route::post('/v1/dealer/listings/fetch-from-url', [AuctionParserController::class, 'fetchFromUrl'])
    ->middleware('auth:sanctum')
    ->name('api.auction.fetch');

Route::post('/v1/dealer/listings/check-parse-status', [AuctionParserController::class, 'checkStatus'])
    ->middleware('auth:sanctum')
    ->name('api.auction.check-status');

// COPART DIRECT+FALLBACK
Route::get('/copart/{id}', function (string $id, CopartService $copart) {
    $result = $copart->getLot($id);
    $response = response()->json($result['data'] ?? null);

    if (!empty($result['fallback'])) {
        $response->header('x-fallback', '1');
    }

    return $response;
})->name('api.copart.show');

// ==================== MOBILE API ====================
Route::prefix('mobile')
    ->as('api.mobile.')
    ->group(function () {
        Route::get('/listings', [MobileListingController::class, 'index'])->name('listings.index');
        Route::get('/listings/{listing}', [MobileListingController::class, 'show'])->name('listings.show');

        Route::prefix('auth')->group(function () {
            Route::post('/phone/send-code', [MobileAuthController::class, 'sendVerificationCode'])
                ->name('auth.phone.send-code');
            Route::post('/register', [MobileAuthController::class, 'register'])->name('auth.register');
            Route::post('/login', [MobileAuthController::class, 'login'])->name('auth.login');
            Route::post('/google', [MobileAuthController::class, 'googleAuth'])->name('auth.google');
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [MobileAuthController::class, 'me'])->name('auth.me');
            Route::post('/auth/logout', [MobileAuthController::class, 'logout'])->name('auth.logout');
            Route::post('/auth/refresh', [MobileAuthController::class, 'refreshToken'])->name('auth.refresh');

            // Safe Deal / Escrow (каркас)
            Route::post('/escrow/create', [EscrowController::class, 'create'])->name('escrow.create');
            Route::get('/escrow', [EscrowController::class, 'index'])->name('escrow.index');
            Route::post('/escrow/capture', [EscrowController::class, 'capture'])->name('escrow.capture');
            Route::post('/escrow/release', [EscrowController::class, 'release'])->name('escrow.release');
            Route::post('/escrow/webhook', [EscrowController::class, 'webhook'])
                ->withoutMiddleware('auth:sanctum')
                ->middleware('escrow.webhook')
                ->name('escrow.webhook');

            // Call Masking (каркас)
            Route::post('/calls/start', [CallMaskingController::class, 'start'])->name('calls.start');
            Route::post('/calls/end', [CallMaskingController::class, 'end'])->name('calls.end');

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

<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Api\CategoryFieldController;
use Illuminate\Http\Request;

// --- ПУБЛИЧНЫЕ МАРШРУТЫ ---

// Главная страница
Route::get('/', [ListingController::class, 'index'])->name('home');

// Используем Route::resource для всех маршрутов объявлений.
// Это автоматически создаст все нужные маршруты в правильном порядке.
Route::resource('listings', ListingController::class);

// Поиск
Route::get('/search', [SearchController::class, 'index'])->name('search.index');


// --- ЗАЩИЩЁННЫЕ МАРШРУТЫ (ТРЕБУЮТ АВТОРИЗАЦИИ) ---

Route::middleware('auth')->group(function () {
    // Личный кабинет
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    // Страница "Мои объявления"
    Route::get('/dashboard/my-listings', [DashboardController::class, 'myListings'])->name('dashboard.my-listings');

    // Управление профилем
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/listings/{listing}/messages', [MessageController::class, 'store'])->name('listings.messages.store');
    Route::post('/messages/reply/{listing}/{participant}', [MessageController::class, 'reply'])->name('messages.reply');

    Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');
    Route::get('/dashboard/messages/{listing}/{participant}', [DashboardController::class, 'showConversation'])->name('dashboard.conversation.show');
    Route::post('/dashboard/messages/{listing}/{participant}', [DashboardController::class, 'replyToConversation'])->name('dashboard.conversation.reply');
    Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite.toggle');
    Route::get('/dashboard/favorites', [DashboardController::class, 'favorites'])->name('dashboard.favorites');
    Route::post('/listings/{listing}/reviews', [ReviewController::class, 'store'])->name('listings.reviews.store');
    Route::get('/categories/{category}/fields', [CategoryFieldController::class, 'index']);


});


// Маршруты для регистрации, входа, сброса пароля и т.д.
require __DIR__.'/auth.php';

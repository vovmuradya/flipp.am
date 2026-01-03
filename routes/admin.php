<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\ErrorLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administrative Routes
|--------------------------------------------------------------------------
|
| Маршруты для административной панели Idrom.am
|
*/

// Группа маршрутов для административной панели
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Проверка прав администратора
    Route::match(['get', 'post'], '/', [DashboardController::class, 'index'])
        ->middleware('permission:view_analytics')
        ->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view_analytics')
        ->name('dashboard');

    // Управление пользователями
    Route::resource('users', UserController::class)
        ->middleware('permission:view_users')
        ->except(['create', 'store']);

    // Управление объявлениями
    Route::resource('listings', ListingController::class)
        ->middleware('permission:view_listings')
        ->except(['create', 'store']);

    // Модерация объявлений
    Route::get('/moderation', [ListingController::class, 'moderation'])
        ->middleware('permission:moderate_listings')
        ->name('moderation.index');
    Route::post('/listings/{id}/approve', [ListingController::class, 'approve'])
        ->middleware('permission:moderate_listings')
        ->name('listings.approve');
    Route::post('/listings/{id}/reject', [ListingController::class, 'reject'])
        ->middleware('permission:moderate_listings')
        ->name('listings.reject');

    // Управление категориями
    Route::resource('categories', CategoryController::class)
        ->middleware('permission:view_categories');

    // Аналитика
    Route::get('/analytics', [AnalyticsController::class, 'index'])
        ->middleware('permission:view_analytics')
        ->name('analytics');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData'])
        ->middleware('permission:view_analytics')
        ->name('analytics.chart-data');
    Route::get('/analytics/detailed', [AnalyticsController::class, 'detailed'])
        ->middleware('permission:view_analytics')
        ->name('analytics.detailed');

    // Настройки
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:manage_settings')
        ->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])
        ->middleware('permission:manage_settings')
        ->name('settings.update');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])
        ->middleware('permission:manage_settings')
        ->name('settings.clear-cache');

    // Поддержка
    Route::get('/support', [SupportController::class, 'index'])
        ->middleware('permission:view_support')
        ->name('support.index');
    Route::post('/support/{id}/impersonate', [SupportController::class, 'impersonate'])
        ->middleware('permission:impersonate_users')
        ->name('support.impersonate');
    Route::post('/support/stop-impersonate', [SupportController::class, 'stopImpersonate'])
        ->name('support.stop-impersonate');
    Route::get('/support/{id}/message', [SupportController::class, 'showMessageForm'])
        ->middleware('permission:manage_support')
        ->name('support.message-form');
    Route::post('/support/{id}/message', [SupportController::class, 'sendMessage'])
        ->middleware('permission:manage_support')
        ->name('support.send-message');

    // Логирование ошибок (только для администраторов)
    Route::get('/errors', [ErrorLogController::class, 'index'])->name('errors.index');
    Route::get('/errors/registration', [ErrorLogController::class, 'registrationErrors'])->name('errors.registration');
    Route::get('/errors/application', [ErrorLogController::class, 'applicationErrors'])->name('errors.application');
    Route::post('/errors/clear-logs', [ErrorLogController::class, 'clearLogs'])->name('errors.clear-logs');

    // Интеграция Idram и imID
    Route::prefix('idram-imid')->name('idram-imid.')->group(function () {
        Route::get('/', [IdramImIDController::class, 'index'])->name('index');
        Route::get('/settings', [IdramImIDController::class, 'settings'])->name('settings');
        Route::put('/settings', [IdramImIDController::class, 'updateSettings'])->name('update-settings');
        Route::get('/transactions', [IdramImIDController::class, 'transactions'])->name('transactions');
        Route::get('/verification-requests', [IdramImIDController::class, 'verificationRequests'])->name('verification-requests');
        Route::put('/verification-requests/{id}', [IdramImIDController::class, 'updateVerificationStatus'])->name('update-verification-status');
    });

    // Тикеты поддержки
    Route::resource('support-tickets', SupportTicketController::class);
    Route::post('/support-tickets/{id}/assign', [SupportTicketController::class, 'assign'])->name('support-tickets.assign');
    Route::post('/support-tickets/{id}/close', [SupportTicketController::class, 'close'])->name('support-tickets.close');

    // Ошибки регистрации
    Route::resource('registration-errors', RegistrationErrorController::class);
    Route::post('/registration-errors/{id}/resolve', [RegistrationErrorController::class, 'resolve'])->name('registration-errors.resolve');
    Route::post('/registration-errors/{id}/unresolve', [RegistrationErrorController::class, 'unresolve'])->name('registration-errors.unresolve');
    Route::post('/registration-errors/cleanup', [RegistrationErrorController::class, 'cleanup'])->name('registration-errors.cleanup');
    Route::get('/registration-errors-statistics', [RegistrationErrorController::class, 'statistics'])->name('registration-errors.statistics');
});
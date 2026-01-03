<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // Для отслеживания сессий
            $table->string('ip_address', 45); // IP-адрес пользователя
            $table->string('user_agent')->nullable(); // User agent
            $table->string('referrer')->nullable(); // Откуда пришёл пользователь
            $table->string('page_url'); // URL страницы
            $table->string('page_title')->nullable(); // Заголовок страницы
            $table->string('country')->nullable(); // Страна пользователя
            $table->string('city')->nullable(); // Город пользователя
            $table->string('device_type')->nullable(); // Тип устройства
            $table->string('browser')->nullable(); // Браузер
            $table->string('platform')->nullable(); // Платформа
            $table->string('utm_source')->nullable(); // UTM параметры
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->timestamp('visited_at')->useCurrent(); // Время посещения
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Если пользователь авторизован
        });

        // Создаем индексы для оптимизации запросов
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['visited_at', 'page_url']);
            $table->index(['user_id', 'visited_at']);
            $table->index(['ip_address', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

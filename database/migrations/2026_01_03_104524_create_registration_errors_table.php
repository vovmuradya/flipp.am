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
        Schema::create('registration_errors', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable(); // Email пользователя, если был введен
            $table->string('username')->nullable(); // Имя пользователя, если было введено
            $table->string('ip_address', 45); // IP-адрес пользователя
            $table->string('user_agent')->nullable(); // User agent
            $table->string('error_type'); // Тип ошибки (validation, duplicate, technical, etc.)
            $table->string('error_code')->nullable(); // Код ошибки
            $table->text('error_message'); // Сообщение об ошибке
            $table->json('request_data')->nullable(); // Данные запроса (без паролей)
            $table->json('validation_errors')->nullable(); // Ошибки валидации
            $table->timestamp('occurred_at')->useCurrent(); // Время возникновения ошибки
            $table->boolean('is_resolved')->default(false); // Решена ли ошибка
            $table->timestamp('resolved_at')->nullable(); // Время решения
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null'); // Кто решил ошибку
            $table->text('resolution_notes')->nullable(); // Заметки о решении
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['error_type', 'occurred_at']);
            $table->index(['email', 'occurred_at']);
            $table->index(['ip_address', 'occurred_at']);
            $table->index(['is_resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_errors');
    }
};

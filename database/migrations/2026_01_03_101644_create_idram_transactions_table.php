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
        Schema::create('idram_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Пользователь, если авторизован
            $table->string('transaction_id')->unique(); // Уникальный ID транзакции от Idram
            $table->string('order_id')->nullable(); // ID заказа в нашей системе
            $table->string('status'); // Статус транзакции (pending, completed, failed, cancelled)
            $table->decimal('amount', 10, 2); // Сумма транзакции
            $table->string('currency', 3)->default('AMD'); // Валюта
            $table->string('description')->nullable(); // Описание транзакции
            $table->string('payment_method')->nullable(); // Метод оплаты
            $table->json('response_data')->nullable(); // Полные данные ответа от Idram
            $table->timestamp('processed_at')->nullable(); // Время обработки
            $table->timestamp('completed_at')->nullable(); // Время завершения
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['transaction_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idram_transactions');
    }
};

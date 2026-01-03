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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь, который создал тикет
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Назначенный агент поддержки
            $table->string('subject'); // Тема тикета
            $table->text('description'); // Описание проблемы
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium'); // Приоритет
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'waiting'])->default('open'); // Статус тикета
            $table->enum('category', [
                'technical', 'billing', 'account', 'listing', 'verification', 'other'
            ])->default('other'); // Категория тикета
            $table->timestamp('resolved_at')->nullable(); // Время решения
            $table->timestamp('closed_at')->nullable(); // Время закрытия
            $table->text('resolution_notes')->nullable(); // Заметки о решении
            $table->integer('replies_count')->default(0); // Количество ответов
            $table->timestamps(); // created_at, updated_at

            // Индексы для оптимизации запросов
            $table->index(['user_id', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

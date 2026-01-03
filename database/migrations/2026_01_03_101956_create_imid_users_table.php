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
        Schema::create('imid_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Пользователь в нашей системе
            $table->string('imid_user_id')->unique(); // Уникальный ID пользователя imID
            $table->string('first_name')->nullable(); // Имя пользователя
            $table->string('last_name')->nullable(); // Фамилия пользователя
            $table->string('email')->nullable(); // Email пользователя
            $table->string('phone')->nullable(); // Телефон пользователя
            $table->string('country')->nullable(); // Страна пользователя
            $table->string('city')->nullable(); // Город пользователя
            $table->string('document_type')->nullable(); // Тип документа
            $table->string('document_number')->nullable(); // Номер документа
            $table->string('document_front')->nullable(); // Изображение лицевой стороны документа
            $table->string('document_back')->nullable(); // Изображение обратной стороны документа
            $table->string('status')->default('pending'); // Статус верификации (pending, verified, rejected)
            $table->timestamp('verified_at')->nullable(); // Время верификации
            $table->json('verification_data')->nullable(); // Дополнительные данные верификации
            $table->timestamp('last_synced_at')->nullable(); // Время последней синхронизации
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['imid_user_id', 'status']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imid_users');
    }
};

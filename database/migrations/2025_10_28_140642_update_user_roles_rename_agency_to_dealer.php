<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Обновляем существующие записи: agency -> dealer
        DB::table('users')
            ->where('role', 'agency')
            ->update(['role' => 'dealer']);
        // Для SQLite нет прямой модификации ENUM, но Laravel автоматически валидирует
        // через модель. Просто обновим данные - валидация будет в модели User.
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откатываем: dealer -> agency
        DB::table('users')
            ->where('role', 'dealer')
            ->update(['role' => 'agency']);
    }
};

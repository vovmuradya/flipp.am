<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            // Разрешаем NULL для базовых полей, чтобы аукционные лоты без данных не падали
            $table->string('make', 100)->nullable()->change();
            $table->string('model', 100)->nullable()->change();
            $table->unsignedInteger('year')->nullable()->change();
            $table->unsignedInteger('mileage')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            // Возвращаем обратно как NOT NULL (по необходимости)
            $table->string('make', 100)->nullable(false)->change();
            $table->string('model', 100)->nullable(false)->change();
            $table->unsignedInteger('year')->nullable(false)->change();
            $table->unsignedInteger('mileage')->nullable(false)->change();
        });
    }
};


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
        Schema::create('vehicle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')
                ->unique()
                ->constrained('listings')
                ->cascadeOnDelete();
            // Основные характеристики автомобиля
            $table->string('make', 100)->comment('Марка');
            $table->string('model', 100)->comment('Модель');
            $table->year('year')->comment('Год выпуска');
            $table->unsignedInteger('mileage')->comment('Пробег в км');
            // Опциональные характеристики
            $table->string('body_type', 50)->nullable()->comment('Тип кузова');
            $table->enum('transmission', ['automatic', 'manual', 'semi-automatic', 'cvt'])
                ->nullable()
                ->comment('Коробка передач');
            $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric', 'lpg'])
                ->nullable()
                ->comment('Тип топлива');
            $table->unsignedInteger('engine_displacement_cc')
                ->nullable()
                ->comment('Объем двигателя, куб. см');
            $table->string('exterior_color', 50)->nullable()->comment('Цвет кузова');
            // Поля для объявлений с аукциона
            $table->boolean('is_from_auction')->default(false)
                ->comment('Флаг: объявление создано с аукциона');
            $table->string('source_auction_url', 512)->nullable()
                ->comment('Ссылка на оригинальный лот');
            $table->timestamps();
            // Индексы для поиска
            $table->index('make');
            $table->index('model');
            $table->index('year');
            $table->index('is_from_auction');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_details');
    }
};

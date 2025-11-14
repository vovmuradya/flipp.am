<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('car_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->constrained('car_models')->cascadeOnDelete();
            $table->string('name'); // <-- ИСПРАВЛЕНО
            $table->string('body_code')->nullable();
            $table->year('year_start')->nullable();
            $table->year('year_end')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('car_generations'); }
};

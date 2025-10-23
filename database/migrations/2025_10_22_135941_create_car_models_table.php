<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_brand_id')->constrained('car_brands')->cascadeOnDelete();
            $table->unsignedBigInteger('nhtsa_id')->nullable();
            $table->string('name_en');
            $table->string('name_ru')->nullable();
            $table->timestamps();
            $table->index('name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};

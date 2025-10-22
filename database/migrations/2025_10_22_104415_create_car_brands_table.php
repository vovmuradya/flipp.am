<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nhtsa_id')->unique()->comment('ID марки в NHTSA API');
            $table->string('name_en')->unique()->comment('Название марки на английском (из API)');
            $table->string('name_ru')->nullable()->comment('Название марки на русском (перевод)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_brands');
    }
};

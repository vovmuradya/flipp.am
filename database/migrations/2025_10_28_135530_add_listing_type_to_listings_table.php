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
        Schema::table('listings', function (Blueprint $table) {
            // Добавляем поле listing_type после category_id
            $table->enum('listing_type', ['vehicle', 'parts'])
                ->default('vehicle')
                ->after('category_id')
                ->comment('Тип объявления: автомобиль или запчасти');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('listing_type');
        });
    }
};

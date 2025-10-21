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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('regions')->nullOnDelete()->name('idx_parent');
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['country', 'city', 'district']); // [cite: 225]
            $table->decimal('latitude', 10, 8)->nullable(); // [cite: 226]
            $table->decimal('longitude', 11, 8)->nullable(); // [cite: 227]
            $table->timestamps();

            $table->index('type', 'idx_type'); // [cite: 231]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};

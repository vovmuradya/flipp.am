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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2); // [cite: 180]
            $table->string('currency', 3)->default('USD'); // [cite: 181, 183]
            $table->enum('status', ['draft', 'active', 'sold', 'expired', 'moderation'])->default('moderation');
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('promoted_until')->nullable();
            $table->timestamp('last_bumped_at')->nullable();
            $table->string('language', 2); // [cite: 189]
            $table->timestamps();
            $table->softDeletes(); // [cite: 193]

            $table->index('status', 'idx_status'); // [cite: 195]
            $table->index('category_id', 'idx_category'); // [cite: 196]
            $table->index('region_id', 'idx_region'); // [cite: 197]
            $table->index('created_at', 'idx_created'); // [cite: 198]
            $table->index('price', 'idx_price'); // [cite: 199]
            $table->index('promoted_until', 'idx_promoted'); // [cite: 200]
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};

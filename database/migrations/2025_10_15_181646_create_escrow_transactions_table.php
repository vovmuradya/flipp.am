<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('escrow_transactions')) {
            return;
        }

        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('hold_id')->unique();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 32)->default('pending'); // pending, held, captured, released, failed
            $table->string('provider', 64)->default('stub');
            $table->json('meta')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'seller_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_transactions');
    }
};

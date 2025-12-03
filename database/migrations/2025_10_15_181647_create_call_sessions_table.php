<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('proxy_number', 32)->nullable();
            $table->string('seller_number', 32)->nullable();
            $table->string('status', 32)->default('pending'); // pending, active, ended, failed
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'seller_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_sessions');
    }
};

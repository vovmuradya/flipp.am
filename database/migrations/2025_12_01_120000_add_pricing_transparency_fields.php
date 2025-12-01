<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('imv_value', 12, 2)->nullable()->after('price');
            $table->decimal('out_the_door_price', 12, 2)->nullable()->after('imv_value');
            $table->string('price_badge', 32)->nullable()->after('currency'); // great/fair/overpriced/unknown
            $table->boolean('price_anomaly')->default(false)->after('price_badge');
            $table->json('price_analysis')->nullable()->after('price_anomaly');

            $table->index('price_badge', 'idx_price_badge');
            $table->index('price_anomaly', 'idx_price_anomaly');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('seller_score', 5, 2)->default(0)->after('is_dealer');
            $table->timestamp('seller_score_calculated_at')->nullable()->after('seller_score');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('idx_price_badge');
            $table->dropIndex('idx_price_anomaly');

            $table->dropColumn([
                'imv_value',
                'out_the_door_price',
                'price_badge',
                'price_anomaly',
                'price_analysis',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'seller_score',
                'seller_score_calculated_at',
            ]);
        });
    }
};

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
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->decimal('buy_now_price', 12, 2)
                ->nullable()
                ->after('source_auction_url')
                ->comment('Buy It Now price provided by the auction');

            $table->string('buy_now_currency', 3)
                ->nullable()
                ->after('buy_now_price')
                ->comment('Currency code for the buy now price');

            $table->string('operational_status', 100)
                ->nullable()
                ->after('buy_now_currency')
                ->comment('Operational status e.g. Run and Drive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->dropColumn([
                'buy_now_price',
                'buy_now_currency',
                'operational_status',
            ]);
        });
    }
};

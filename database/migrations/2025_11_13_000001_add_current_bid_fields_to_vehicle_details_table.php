<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicle_details', 'current_bid_price')) {
                $table->decimal('current_bid_price', 12, 2)
                    ->nullable()
                    ->after('buy_now_currency');
            }

            if (! Schema::hasColumn('vehicle_details', 'current_bid_currency')) {
                $table->string('current_bid_currency', 5)
                    ->nullable()
                    ->after('current_bid_price');
            }

            if (! Schema::hasColumn('vehicle_details', 'current_bid_fetched_at')) {
                $table->timestamp('current_bid_fetched_at')
                    ->nullable()
                    ->after('current_bid_currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_details', 'current_bid_fetched_at')) {
                $table->dropColumn('current_bid_fetched_at');
            }
            if (Schema::hasColumn('vehicle_details', 'current_bid_currency')) {
                $table->dropColumn('current_bid_currency');
            }
            if (Schema::hasColumn('vehicle_details', 'current_bid_price')) {
                $table->dropColumn('current_bid_price');
            }
        });
    }
};

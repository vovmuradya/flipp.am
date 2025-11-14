<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_details', 'auction_ends_at')) {
                $table->dateTime('auction_ends_at')
                    ->nullable()
                    ->after('source_auction_url')
                    ->comment('Дата и время окончания торгов');
                $table->index('auction_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_details', 'auction_ends_at')) {
                $table->dropIndex('vehicle_details_auction_ends_at_index');
                $table->dropColumn('auction_ends_at');
            }
        });
    }
};

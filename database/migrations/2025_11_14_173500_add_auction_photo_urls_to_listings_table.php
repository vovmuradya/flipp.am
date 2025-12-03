<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'auction_photo_urls')) {
                $table->json('auction_photo_urls')
                    ->nullable()
                    ->after('is_from_auction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'auction_photo_urls')) {
                $table->dropColumn('auction_photo_urls');
            }
        });
    }
};

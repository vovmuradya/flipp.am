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
            if (!Schema::hasColumn('vehicle_details', 'preview_image_url')) {
                $table->string('preview_image_url', 512)
                    ->nullable()
                    ->after('source_auction_url')
                    ->comment('URL предварительного изображения с аукциона');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_details', 'preview_image_url')) {
                $table->dropColumn('preview_image_url');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->string('primary_damage', 150)
                ->nullable()
                ->after('operational_status')
                ->comment('Primary damage from auction');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->dropColumn('primary_damage');
        });
    }
};

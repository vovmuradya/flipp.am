<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_profiles', function (Blueprint $table) {
            // slug уже существует — НЕ добавляем

            if (!Schema::hasColumn('dealer_profiles', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('logo');
            }

            if (!Schema::hasColumn('dealer_profiles', 'description')) {
                $table->text('description')->nullable()->after('logo_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dealer_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('dealer_profiles', 'logo_url')) {
                $table->dropColumn('logo_url');
            }

            if (Schema::hasColumn('dealer_profiles', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};

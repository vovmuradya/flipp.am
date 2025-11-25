<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'refreshed_at')) {
                $table->dateTime('refreshed_at')->nullable()->after('updated_at');
                $table->index('refreshed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'refreshed_at')) {
                $table->dropIndex(['refreshed_at']);
                $table->dropColumn('refreshed_at');
            }
        });
    }
};

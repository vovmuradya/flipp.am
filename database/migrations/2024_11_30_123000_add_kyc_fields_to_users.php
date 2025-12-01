<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $afterColumn = Schema::hasColumn('users', 'seller_score_calculated_at')
            ? 'seller_score_calculated_at'
            : 'email';

        Schema::table('users', function (Blueprint $table) use ($afterColumn) {
            $table->string('kyc_status', 32)->default('unverified')->after($afterColumn);
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kyc_status', 'kyc_verified_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('category_fields')
            ->where('key', 'model')
            ->update(['type' => 'select']);
    }

    public function down(): void
    {
        DB::table('category_fields')
            ->where('key', 'model')
            ->update(['type' => 'text']);
    }
};
